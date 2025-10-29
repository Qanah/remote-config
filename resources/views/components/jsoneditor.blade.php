{{--
    JSONEditor Component - Edit Mode

    Usage:
    @include('remote-config::components.jsoneditor', [
        'name' => 'content',           // Textarea name attribute
        'value' => $flow->content,     // JSON data (array or string)
        'height' => '500px',           // Optional: Editor height (default: 500px)
        'required' => true,            // Optional: Make field required (default: false)
    ])
--}}

@php
    $editorId = 'jsoneditor_' . Str::random(8);
    $textareaId = 'textarea_' . Str::random(8);
    $height = $height ?? '500px';
    $required = $required ?? false;
    $value = is_array($value ?? null) ? json_encode($value) : ($value ?? '{}');
@endphp

<div class="jsoneditor-wrapper">
    <div id="{{ $editorId }}" style="height: {{ $height }};"></div>
    <textarea name="{{ $name }}" id="{{ $textareaId }}" class="hidden" {{ $required ? 'required' : '' }}>{{ old($name, $value) }}</textarea>
</div>

@once
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/jsoneditor@9.10.0/dist/jsoneditor.min.css" rel="stylesheet" type="text/css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsoneditor@9.10.0/dist/jsoneditor.min.js"></script>
@endpush
@endonce

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('{{ $editorId }}');
        const textarea = document.getElementById('{{ $textareaId }}');

        if (!container || !textarea) {
            console.error('JSONEditor container or textarea not found');
            return;
        }

        const options = {
            mode: 'tree',
            modes: ['tree', 'code', 'form', 'text', 'view'],
            onChangeText: function (jsonString) {
                textarea.value = jsonString;
            },
            onValidationError: function (errors) {
                console.warn('JSON validation errors:', errors);
            }
        };

        const editor = new JSONEditor(container, options);

        // Set initial JSON
        try {
            const initialJson = JSON.parse(textarea.value);
            editor.set(initialJson);
        } catch (e) {
            console.warn('Invalid initial JSON, setting empty object:', e);
            editor.set({});
        }

        // Update textarea on form submit
        const form = container.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                try {
                    const json = editor.get();
                    textarea.value = JSON.stringify(json);
                    console.log('Form submitting with JSON:', json);
                } catch (err) {
                    e.preventDefault();
                    alert('Invalid JSON: ' + err.message);
                    console.error('JSON validation error:', err);
                }
            });
        }
    });
</script>
@endpush
