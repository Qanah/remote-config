@extends('remote-config::layouts.app')

@section('page-title', 'Edit Flow #' . $flow->id)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Edit Flow #{{ $flow->id }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ $flow->type }} - Last updated {{ $flow->updated_at->diffForHumans() }}
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0 gap-x-3">
            <a href="{{ route('remote-config.flows.show', $flow) }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                View
            </a>
            <a href="{{ route('remote-config.flows.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('remote-config.flows.update', $flow) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <!-- Type -->
                    <div class="sm:col-span-3">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                        <select id="type" name="type" required class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                            <option value="">Select type...</option>
                            @foreach($flowTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $flow->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $flow->name) }}" required class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2" placeholder="e.g., control, variant-a">
                        <p class="mt-2 text-sm text-gray-500">Unique name for this flow within the same type (e.g., control, variant-a, variant-b).</p>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="sm:col-span-6">
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" {{ old('is_active', $flow->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600 focus:ring-primary-600">
                            <label for="is_active" class="ml-3 block text-sm font-medium leading-6 text-gray-900">
                                Active
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Inactive flows cannot be used in experiments.</p>
                    </div>

                    <div class="sm:col-span-6">
                        <div class="flex items-center">
                            <input id="is_default" name="is_default" type="checkbox" {{ old('is_default', $flow->is_default) ? 'checked' : '' }} class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600 focus:ring-primary-600">
                            <label for="is_default" class="ml-3 block text-sm font-medium leading-6 text-gray-900">
                                Set as Default
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Mark this flow as the default configuration for this type. Only one default per type is allowed.</p>
                        @error('is_default')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- JSON Content -->
                    <div class="sm:col-span-6">
                        <label for="content" class="block text-sm font-medium text-gray-700">JSON Content *</label>
                        <div class="mt-1">
                            @include('remote-config::components.jsoneditor', [
                                'name' => 'content',
                                'value' => $flow->content,
                                'height' => '500px',
                                'required' => true
                            ])
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Configure your JSON settings using the visual editor above.</p>
                        @error('content')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <button type="button" onclick="if(confirm('Are you sure you want to delete this flow?')) { document.getElementById('delete-form').submit(); }" class="text-sm font-semibold text-red-600 hover:text-red-500">
                    Delete Flow
                </button>
                <div class="flex gap-x-6">
                    <a href="{{ route('remote-config.flows.show', $flow) }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                    <button type="submit" class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                        Update Flow
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Form -->
    <form id="delete-form" action="{{ route('remote-config.flows.destroy', $flow) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
