@extends('remote-config::layouts.app')

@section('page-title', 'Experiment Details')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900">
                {{ $experiment->name }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Experiment #{{ $experiment->id }} • {{ $experiment->type }}
            </p>
        </div>
        <div class="mt-4 flex gap-2 md:ml-4 md:mt-0">
            <form action="{{ route('remote-config.experiments.toggle', $experiment) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm {{ $experiment->is_active ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                    {{ $experiment->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('remote-config.experiments.edit', $experiment) }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Edit
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total Assignments</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['total_assignments']) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total Selections</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['total_selections']) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Confirmations</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['confirmations']) }}</dd>
        </div>
    </div>

    <!-- Details -->
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <dl class="divide-y divide-gray-100">
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Status</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $experiment->is_active ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10' }}">
                            {{ $experiment->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Platforms</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        {{ implode(', ', $experiment->platforms) }}
                    </dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Countries</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        {{ implode(', ', $experiment->countries) }}
                    </dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Languages</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        {{ implode(', ', $experiment->languages) }}
                    </dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Flows (Variants)</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <ul class="divide-y divide-gray-100 rounded-md border border-gray-200">
                            @foreach($experiment->flows as $flow)
                                <li class="flex items-center justify-between py-3 pl-3 pr-4 text-sm">
                                    <div class="flex w-0 flex-1 items-center">
                                        <span class="ml-2 w-0 flex-1 truncate">Flow #{{ $flow->id }} - {{ $flow->type }}</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="font-medium text-primary-600">Ratio: {{ $flow->pivot->ratio }}%</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Assignment Stats -->
    @if($assignmentStats['total_assignments'] > 0)
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Flow Distribution</h3>
            <div class="space-y-3">
                @foreach($experiment->flows as $flow)
                    @php
                        $flowAssignments = $assignmentStats['by_flow'][$flow->id] ?? 0;
                        $percentage = $assignmentStats['total_assignments'] > 0 ? round(($flowAssignments / $assignmentStats['total_assignments']) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Flow #{{ $flow->id }}</span>
                            <span class="text-sm text-gray-500">{{ number_format($flowAssignments) }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection