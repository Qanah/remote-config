@extends('remote-config::layouts.app')

@section('page-title', 'Flows')

@section('content')
<div class="space-y-6">
    <!-- Header with stats -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Configuration Flows
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Manage JSON configuration variants for A/B testing
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('remote-config.flows.create') }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Flow
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total Flows</dt>
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
            <form method="GET" action="{{ route('remote-config.flows.index') }}" class="space-y-4">
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
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="ID, type, or content..." class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-base px-3 py-2">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Flows table -->
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">ID</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Content Preview</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Updated</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($flows as $flow)
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                        #{{ $flow->id }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                            {{ $flow->type }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                        {{ $flow->name }}
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500 max-w-md truncate">
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                            {{ Str::limit(json_encode($flow->content), 80) }}
                        </code>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <form action="{{ route('remote-config.flows.toggle', $flow) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $flow->is_active ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10' }}">
                                {{ $flow->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        {{ $flow->updated_at->diffForHumans() }}
                    </td>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <a href="{{ route('remote-config.flows.show', $flow) }}" class="text-primary-600 hover:text-primary-900 mr-4">View</a>
                        <a href="{{ route('remote-config.flows.edit', $flow) }}" class="text-primary-600 hover:text-primary-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No flows found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new flow.</p>
                        <div class="mt-6">
                            <a href="{{ route('remote-config.flows.create') }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Flow
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($flows->hasPages())
        <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            {{ $flows->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
