{{--
    JSONViewer Component - View-Only Mode

    Usage:
    @include('remote-config::components.jsonviewer', [
        'data' => $flow->content,      // JSON data (array or object)
        'height' => '400px',           // Optional: Viewer height (default: 400px)
        'modes' => ['view', 'tree'],   // Optional: Available modes (default: ['view', 'code', 'tree'])
    ])
--}}

@php
    $viewerId = 'jsonviewer_' . Str::random(8);
    $height = $height ?? '400px';
    $modes = $modes ?? ['view', 'code', 'tree'];
    $data = $data ?? [];
@endphp

<div class="jsonviewer-wrapper">
    <div id="{{ $viewerId }}" style="height: {{ $height }};"></div>
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
        const container = document.getElementById('{{ $viewerId }}');

        if (!container) {
            console.error('JSONViewer container not found');
            return;
        }

        const options = {
            mode: '{{ $modes[0] ?? "view" }}',
            modes: @json($modes),
            navigationBar: true,
            statusBar: true,
            search: true,
            onError: function (err) {
                console.error('JSONViewer error:', err);
            }
        };

        const viewer = new JSONEditor(container, options);

        try {
            viewer.set(@json($data));
        } catch (e) {
            console.error('Error setting JSON data:', e);
            viewer.set({});
        }
    });
</script>
@endpush
