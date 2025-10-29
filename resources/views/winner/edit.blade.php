@extends('remote-config::layouts.app')

@section('page-title', 'Edit Winner')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Edit Winner
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Update winner configuration
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('remote-config.winners.show', $winner) }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('remote-config.winners.update', $winner) }}" class="space-y-6">
        @csrf
        @method('PATCH')
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                        <select id="type" name="type" required class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                            @foreach($flowTypes as $key => $label)
                                <option value="{{ $key }}" {{ $winner->type === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="platform" class="block text-sm font-medium text-gray-700">Platform *</label>
                        <select id="platform" name="platform" required class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                            @foreach($platforms as $key => $label)
                                <option value="{{ $key }}" {{ $winner->platform === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="country_code" class="block text-sm font-medium text-gray-700">Country *</label>
                        <select id="country_code" name="country_code" required class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                            @foreach($countries as $key => $label)
                                <option value="{{ $key }}" {{ $winner->country_code === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="language" class="block text-sm font-medium text-gray-700">Language *</label>
                        <select id="language" name="language" required class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                            @foreach($languages as $key => $label)
                                <option value="{{ $key }}" {{ $winner->language === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-full">
                        <label for="flow_id" class="block text-sm font-medium text-gray-700">Flow (Optional)</label>
                        <select id="flow_id" name="flow_id" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                            <option value="">None - Use custom content</option>
                            @foreach($flows as $flow)
                                <option value="{{ $flow->id }}" {{ $winner->flow_id == $flow->id ? 'selected' : '' }}>Flow #{{ $flow->id }} - {{ $flow->type }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500">Optional. Link to an existing flow or use custom JSON content below.</p>
                    </div>
                    <div class="sm:col-span-full">
                        <label for="content" class="block text-sm font-medium text-gray-700">Content (JSON) *</label>
                        <div class="mt-1">
                            @include('remote-config::components.jsoneditor', [
                                'name' => 'content',
                                'value' => $winner->content,
                                'height' => '500px',
                                'required' => true
                            ])
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Configure your JSON settings using the visual editor above.</p>
                    </div>
                    <div class="sm:col-span-full">
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" {{ $winner->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600">
                            <label for="is_active" class="ml-3 text-sm text-gray-900">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <a href="{{ route('remote-config.winners.show', $winner) }}" class="text-sm font-semibold text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">Update</button>
            </div>
        </div>
    </form>
</div>
@endsection
