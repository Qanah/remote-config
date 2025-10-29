@extends('remote-config::layouts.app')

@section('page-title', 'Create Flow')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Create New Flow
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Create a new JSON configuration variant
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('remote-config.flows.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('remote-config.flows.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <!-- Type -->
                    <div class="sm:col-span-3">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                        <select id="type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                            <option value="">Select type...</option>
                            @foreach($flowTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Overwrite ID -->
                    <div class="sm:col-span-3">
                        <label for="overwrite_id" class="block text-sm font-medium text-gray-700">Overwrite ID</label>
                        <input type="number" name="overwrite_id" id="overwrite_id" value="{{ old('overwrite_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                        <p class="mt-2 text-sm text-gray-500">Optional. Allows multiple experiments on same base config.</p>
                        @error('overwrite_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="sm:col-span-6">
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                            <label for="is_active" class="ml-3 block text-sm font-medium leading-6 text-gray-900">
                                Active
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Inactive flows cannot be used in experiments.</p>
                    </div>

                    <!-- JSON Content -->
                    <div class="sm:col-span-6">
                        <label for="content" class="block text-sm font-medium text-gray-700">JSON Content *</label>
                        <div class="mt-1">
                            <div id="jsoneditor" style="height: 500px;"></div>
                            <textarea name="content" id="content" class="hidden" required>{{ old('content', '{}') }}</textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Configure your JSON settings using the visual editor above.</p>
                        @error('content')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <a href="{{ route('remote-config.flows.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    Create Flow
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/jsoneditor@9.10.0/dist/jsoneditor.min.css" rel="stylesheet" type="text/css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsoneditor@9.10.0/dist/jsoneditor.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize JSONEditor
        const container = document.getElementById('jsoneditor');
        const textarea = document.getElementById('content');

        if (!container || !textarea) {
            console.error('JSONEditor container or textarea not found');
            return;
        }

        const options = {
            mode: 'tree',
            modes: ['tree', 'code', 'form', 'text', 'view'],
            onChangeText: function (jsonString) {
                textarea.value = jsonString;
            }
        };

        const editor = new JSONEditor(container, options);

        // Set initial JSON
        try {
            const initialJson = JSON.parse(textarea.value);
            editor.set(initialJson);
        } catch (e) {
            editor.set({});
        }

        // Update textarea on form submit
        const form = document.querySelector('form[action*="flows.store"]');
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
