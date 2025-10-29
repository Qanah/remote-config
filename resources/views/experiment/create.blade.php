@extends('remote-config::layouts.app')

@section('page-title', 'Create Experiment')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900">
                Create New Experiment
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Set up a new A/B test experiment with multiple variants
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('remote-config.experiments.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('remote-config.experiments.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Experiment Name</label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <div class="mt-1">
                            <select id="type" name="type" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                                <option value="">Select type</option>
                                @foreach($flowTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-full">
                        <label class="block text-sm font-medium text-gray-700">Platforms</label>
                        <div class="mt-2 space-y-2">
                            @foreach($platforms as $key => $label)
                                <div class="flex items-center">
                                    <input id="platform-{{ $key }}" name="platforms[]" value="{{ $key }}" type="checkbox" {{ in_array($key, old('platforms', [])) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                                    <label for="platform-{{ $key }}" class="ml-3 text-sm leading-6 text-gray-600">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('platforms')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-full">
                        <label class="block text-sm font-medium text-gray-700">Countries</label>
                        <div class="mt-2 grid grid-cols-3 gap-4">
                            @foreach($countries as $key => $label)
                                <div class="flex items-center">
                                    <input id="country-{{ $key }}" name="countries[]" value="{{ $key }}" type="checkbox" {{ in_array($key, old('countries', [])) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                                    <label for="country-{{ $key }}" class="ml-3 text-sm leading-6 text-gray-600">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('countries')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-full">
                        <label class="block text-sm font-medium text-gray-700">Languages</label>
                        <div class="mt-2 space-y-2">
                            @foreach($languages as $key => $label)
                                <div class="flex items-center">
                                    <input id="language-{{ $key }}" name="languages[]" value="{{ $key }}" type="checkbox" {{ in_array($key, old('languages', [])) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                                    <label for="language-{{ $key }}" class="ml-3 text-sm leading-6 text-gray-600">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('languages')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-full">
                        <label for="user_created_after_date" class="block text-sm font-medium text-gray-700">User Created After Date (Optional)</label>
                        <div class="mt-1">
                            <input type="date" name="user_created_after_date" id="user_created_after_date" value="{{ old('user_created_after_date', config('remote-config.user_created_after_date')) }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Only users created after this date will be included</p>
                    </div>

                    <div class="sm:col-span-full" x-data="{ flows: [{ id: '', ratio: 50 }, { id: '', ratio: 50 }] }">
                        <label class="block text-sm font-medium leading-6 text-gray-900 mb-4">Experiment Variants (Flows)</label>
                        <template x-for="(flow, index) in flows" :key="index">
                            <div class="flex gap-4 mb-3">
                                <div class="flex-1">
                                    <select :name="'flows[' + index + '][id]'" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                                        <option value="">Select Flow</option>
                                        @foreach($flows as $flow)
                                            <option value="{{ $flow->id }}">{{ $flow->type }} (ID: {{ $flow->id }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-32">
                                    <input type="number" :name="'flows[' + index + '][ratio]'" x-model="flow.ratio" required min="1" max="100" placeholder="Ratio" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                                </div>
                                <button type="button" @click="flows.length > 2 && flows.splice(index, 1)" :disabled="flows.length <= 2" class="px-3 py-2 text-sm text-red-600 hover:text-red-800 disabled:text-gray-400">Remove</button>
                            </div>
                        </template>
                        <button type="button" @click="flows.push({ id: '', ratio: 50 })" class="mt-2 inline-flex items-center rounded-md bg-primary-50 px-3 py-2 text-sm font-semibold text-primary-600 hover:bg-primary-100">
                            + Add Variant
                        </button>
                        <p class="mt-2 text-sm text-gray-500">Ratios determine the distribution percentage. Must have at least 2 variants.</p>
                    </div>

                    <div class="sm:col-span-full">
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" {{ old('is_active') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                            <label for="is_active" class="ml-3 text-sm leading-6 text-gray-900">Active (start experiment immediately)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <a href="{{ route('remote-config.experiments.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Create Experiment</button>
            </div>
        </div>
    </form>
</div>
@endsection