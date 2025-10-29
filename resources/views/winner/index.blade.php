@extends('remote-config::layouts.app')

@section('page-title', 'Winners')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Experiment Winners
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Lock winning variants for specific platforms/countries
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('remote-config.winners.create') }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Winner
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total Winners</dt>
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

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">ID</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Platform</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Country</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Language</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Flow</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($winners as $winner)
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#{{ $winner->id }}</td>
                    <td class="px-3 py-4 text-sm text-gray-500">
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">{{ $winner->type }}</span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $winner->platform }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $winner->country_code }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $winner->language }}</td>
                    <td class="px-3 py-4 text-sm text-gray-500">
                        @if($winner->flow)
                            Flow #{{ $winner->flow_id }}
                        @else
                            <span class="text-gray-400">Custom content</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                        <form action="{{ route('remote-config.winners.toggle', $winner) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $winner->is_active ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10' }}">
                                {{ $winner->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <a href="{{ route('remote-config.winners.show', $winner) }}" class="text-primary-600 hover:text-primary-900 mr-4">View</a>
                        <a href="{{ route('remote-config.winners.edit', $winner) }}" class="text-primary-600 hover:text-primary-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-3 py-8 text-center text-sm text-gray-500">
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No winners found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a winner.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($winners->hasPages())
        <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            {{ $winners->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
