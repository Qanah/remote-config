@extends('remote-config::layouts.app')

@section('page-title', 'Flow #' . $flow->id)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Flow #{{ $flow->id }}
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                        {{ $flow->type }}
                    </span>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <span class="{{ $flow->is_active ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $flow->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0 gap-x-3">
            <a href="{{ route('remote-config.flows.edit', $flow) }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                Edit
            </a>
            <a href="{{ route('remote-config.flows.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Used in Experiments</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['experiments_count'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">User Assignments</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['assignments_count'] }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Winner Deployments</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['winners_count'] }}</dd>
        </div>
    </div>

    <!-- JSON Content -->
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">JSON Content</h3>
            <div id="jsonviewer" style="height: 400px;"></div>
        </div>
    </div>

    <!-- Related Experiments -->
    @if($experiments->isNotEmpty())
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Used in Experiments</h3>
            <div class="mt-4 space-y-4">
                @foreach($experiments as $experiment)
                <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">{{ $experiment->name }}</h4>
                        <p class="text-sm text-gray-500">Ratio: {{ $experiment->pivot->ratio }}% | {{ $experiment->assignments->count() }} assignments</p>
                    </div>
                    <a href="{{ route('remote-config.experiments.show', $experiment) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        View →
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Change History -->
    @if($flow->logs->isNotEmpty())
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Change History</h3>
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($flow->logs as $log)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Modified by <span class="font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</span>
                                        </p>
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-sm text-primary-600 hover:text-primary-500">View changes</summary>
                                            <pre class="mt-2 text-xs bg-gray-50 p-2 rounded overflow-auto">{{ json_encode($log->log_info, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/jsoneditor@9.10.0/dist/jsoneditor.min.css" rel="stylesheet" type="text/css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsoneditor@9.10.0/dist/jsoneditor.min.js"></script>
<script>
    // Initialize JSONEditor in view-only mode
    const container = document.getElementById('jsonviewer');

    const options = {
        mode: 'view',
        modes: ['view', 'code', 'tree'],
    };

    const editor = new JSONEditor(container, options);
    editor.set(@json($flow->content));
</script>
@endpush
