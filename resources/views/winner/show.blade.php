@extends('remote-config::layouts.app')

@section('page-title', 'Winner Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <dl class="divide-y divide-gray-100">
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Type</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2">{{ $winner->type }}</dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Platform</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2">{{ $winner->platform }}</dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Country</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2">{{ $winner->country_code }}</dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Language</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2">{{ $winner->language }}</dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Content</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2">
                        @include('remote-config::components.jsonviewer', [
                            'data' => $winner->content,
                            'height' => '400px',
                            'modes' => ['view', 'code', 'tree']
                        ])
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
