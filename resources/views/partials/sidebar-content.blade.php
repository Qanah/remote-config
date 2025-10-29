@php
    use Jawabapp\RemoteConfig\Models\Flow;
    use Jawabapp\RemoteConfig\Models\Experiment;
    use Jawabapp\RemoteConfig\Models\Winner;

    $flowsCount = Flow::count();
    $activeExperimentsCount = Experiment::where('is_active', true)->count();
    $winnersCount = Winner::count();
@endphp

<div class="flex h-16 shrink-0 items-center {{ request()->routeIs('remote-config.*') ? 'border-b border-gray-200' : '' }}">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="h-10 w-10 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
        </div>
        <div class="ml-4">
            <h1 class="text-xl font-bold text-gray-900">Remote Config</h1>
            <p class="text-sm text-gray-500">Experiments & A/B Testing</p>
        </div>
    </div>
</div>

<nav class="flex flex-1 flex-col">
    <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
            <div class="text-xs font-semibold leading-6 text-gray-400 uppercase tracking-wider mb-2">Management</div>
            <ul role="list" class="-mx-2 space-y-1">
                <li>
                    <a href="{{ route('remote-config.flows.index') }}"
                       class="{{ request()->routeIs('remote-config.flows.*') ? 'bg-primary-50 text-primary-700 border-r-4 border-primary-500' : 'text-gray-700 hover:text-primary-700 hover:bg-gray-50' }} group flex gap-x-3 rounded-lg p-3 text-sm leading-6 font-medium transition-all duration-200">
                        <svg class="{{ request()->routeIs('remote-config.flows.*') ? 'text-primary-500' : 'text-gray-400 group-hover:text-primary-500' }} h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Flows
                        <span class="{{ request()->routeIs('remote-config.flows.*') ? 'bg-primary-100 text-primary-600' : 'bg-gray-100 text-gray-600' }} ml-auto w-9 min-w-max whitespace-nowrap rounded-full px-2.5 py-0.5 text-center text-xs font-medium leading-5 transition-colors duration-200">{{ $flowsCount }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('remote-config.experiments.index') }}"
                       class="{{ request()->routeIs('remote-config.experiments.*') ? 'bg-primary-50 text-primary-700 border-r-4 border-primary-500' : 'text-gray-700 hover:text-primary-700 hover:bg-gray-50' }} group flex gap-x-3 rounded-lg p-3 text-sm leading-6 font-medium transition-all duration-200">
                        <svg class="{{ request()->routeIs('remote-config.experiments.*') ? 'text-primary-500' : 'text-gray-400 group-hover:text-primary-500' }} h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        Experiments
                        <span class="{{ request()->routeIs('remote-config.experiments.*') ? 'bg-primary-100 text-primary-600' : 'bg-gray-100 text-gray-600' }} ml-auto w-9 min-w-max whitespace-nowrap rounded-full px-2.5 py-0.5 text-center text-xs font-medium leading-5 transition-colors duration-200">{{ $activeExperimentsCount }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('remote-config.winners.index') }}"
                       class="{{ request()->routeIs('remote-config.winners.*') ? 'bg-primary-50 text-primary-700 border-r-4 border-primary-500' : 'text-gray-700 hover:text-primary-700 hover:bg-gray-50' }} group flex gap-x-3 rounded-lg p-3 text-sm leading-6 font-medium transition-all duration-200">
                        <svg class="{{ request()->routeIs('remote-config.winners.*') ? 'text-primary-500' : 'text-gray-400 group-hover:text-primary-500' }} h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        Winners
                        <span class="{{ request()->routeIs('remote-config.winners.*') ? 'bg-primary-100 text-primary-600' : 'bg-gray-100 text-gray-600' }} ml-auto w-9 min-w-max whitespace-nowrap rounded-full px-2.5 py-0.5 text-center text-xs font-medium leading-5 transition-colors duration-200">{{ $winnersCount }}</span>
                    </a>
                </li>
            </ul>
        </li>

        @if(config('remote-config.testing_enabled', true))
        <li>
            <div class="text-xs font-semibold leading-6 text-gray-400 uppercase tracking-wider mb-2">QA & Testing</div>
            <ul role="list" class="-mx-2 space-y-1">
                <li>
                    <a href="{{ route('remote-config.testing.index') }}"
                       class="{{ request()->routeIs('remote-config.testing.*') ? 'bg-primary-50 text-primary-700 border-r-4 border-primary-500' : 'text-gray-700 hover:text-primary-700 hover:bg-gray-50' }} group flex gap-x-3 rounded-lg p-3 text-sm leading-6 font-medium transition-all duration-200">
                        <svg class="{{ request()->routeIs('remote-config.testing.*') ? 'text-primary-500' : 'text-gray-400 group-hover:text-primary-500' }} h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        Test Overrides
                    </a>
                </li>
            </ul>
        </li>
        @endif

        <li class="mt-auto">
            <div class="bg-gradient-to-r from-primary-50 to-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Need Help?</p>
                        <p class="text-xs text-gray-500">Check our documentation</p>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</nav>
