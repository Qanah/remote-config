@extends('remote-config::layouts.app')

@section('page-title', 'Experiments')

@section('content')
<div class="space-y-6">
    <!-- Header with stats -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                A/B Test Experiments
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Manage A/B tests and experiments across different user segments
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('remote-config.experiments.create') }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Experiment
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total Experiments</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['total'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Active</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-600">{{ $stats['active'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Inactive</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-500">{{ $stats['inactive'] }}</dd>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" action="{{ route('remote-config.experiments.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select id="type" name="type" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-base px-3 py-2">
                            <option value="">All Types</option>
                            @foreach($flowTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-base px-3 py-2">
                            <option value="">All</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label for="platform" class="block text-sm font-medium text-gray-700">Platform</label>
                        <select id="platform" name="platform" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-base px-3 py-2">
                            <option value="">All Platforms</option>
                            @foreach($platforms as $key => $label)
                                <option value="{{ $key }}" {{ request('platform') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                        <select id="country" name="country" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-base px-3 py-2">
                            <option value="">All Countries</option>
                            @foreach($countries as $key => $label)
                                <option value="{{ $key }}" {{ request('country') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700">Language</label>
                        <select id="language" name="language" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-base px-3 py-2">
                            <option value="">All Languages</option>
                            @foreach($languages as $key => $label)
                                <option value="{{ $key }}" {{ request('language') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="ID or name..." class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-base px-3 py-2">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex flex-1 justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                            Filter
                        </button>
                        @if(request()->hasAny(['type', 'status', 'platform', 'country', 'language', 'search']))
                            <a href="{{ route('remote-config.experiments.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Experiments table -->
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Experiment</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Targeting</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Variants</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($experiments as $experiment)
                <tr class="hover:bg-gray-50">
                    <td class="py-4 pl-4 pr-3 sm:pl-6">
                        <div class="flex items-center">
                            <div>
                                <div class="font-medium text-gray-900">#{{ $experiment->id }} {{ $experiment->name }}</div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                        {{ $experiment->type }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-4">
                        <div class="space-y-1">
                            <div class="flex flex-wrap gap-1">
                                @foreach($experiment->platforms as $platform)
                                    <span class="inline-flex items-center rounded bg-purple-50 px-1.5 py-0.5 text-xs font-medium text-purple-700" title="Platform">
                                        {{ $platform }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($experiment->countries, 0, 3) as $country)
                                    <span class="inline-flex items-center rounded bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700" title="Country">
                                        {{ $country }}
                                    </span>
                                @endforeach
                                @if(count($experiment->countries) > 3)
                                    <span class="inline-flex items-center rounded bg-gray-50 px-1.5 py-0.5 text-xs font-medium text-gray-600" title="{{ implode(', ', $experiment->countries) }}">
                                        +{{ count($experiment->countries) - 3 }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($experiment->languages as $language)
                                    <span class="inline-flex items-center rounded bg-orange-50 px-1.5 py-0.5 text-xs font-medium text-orange-700" title="Language">
                                        {{ $language }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500">
                        <div class="text-sm font-medium text-gray-900">{{ $experiment->flows->count() }} variants</div>
                        <div class="text-xs text-gray-500">{{ $experiment->assignments->count() }} assignments</div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4">
                        <form action="{{ route('remote-config.experiments.toggle', $experiment) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $experiment->is_active ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10' }}">
                                {{ $experiment->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                        <div class="text-xs text-gray-500 mt-1">{{ $experiment->updated_at->diffForHumans() }}</div>
                    </td>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <a href="{{ route('remote-config.experiments.show', $experiment) }}" class="text-primary-600 hover:text-primary-900 mr-3">View</a>
                        <a href="{{ route('remote-config.experiments.edit', $experiment) }}" class="text-primary-600 hover:text-primary-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No experiments found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new experiment.</p>
                        <div class="mt-6">
                            <a href="{{ route('remote-config.experiments.create') }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Experiment
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($experiments->hasPages())
        <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            {{ $experiments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection