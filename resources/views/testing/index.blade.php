@extends('remote-config::layouts.app')

@section('page-title', 'Testing Overrides')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900">Testing Overrides</h2>
            <p class="mt-1 text-sm text-gray-500">Override config for specific IP addresses</p>
        </div>
    </div>

    <form method="POST" action="{{ route('remote-config.testing.store') }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        @csrf
        <div class="px-4 py-6 sm:p-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-4">
                <div>
                    <label for="ip" class="block text-sm font-medium text-gray-900">IP Address</label>
                    <input type="text" name="ip" id="ip" required placeholder="192.168.1.1" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-900">Type</label>
                    <select id="type" name="type" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300">
                        @foreach($flowTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="flow_id" class="block text-sm font-medium text-gray-900">Flow</label>
                    <select id="flow_id" name="flow_id" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300">
                        @foreach($flows as $flow)
                            <option value="{{ $flow->id }}">Flow #{{ $flow->id }} - {{ $flow->type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">Add Override</button>
                </div>
            </div>
        </div>
    </form>

    @if(count($overridesByType) > 0)
        @foreach($overridesByType as $typeKey => $typeData)
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">{{ $typeData['name'] }}</h3>
                <div class="space-y-2">
                    @foreach($typeData['overrides'] as $ip => $flowId)
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-gray-900">{{ $ip }}</span>
                            <span class="text-sm text-gray-500 ml-4">→ Flow #{{ $flowId }}</span>
                        </div>
                        <form action="{{ route('remote-config.testing.destroy', [str_replace('.', '_', $ip), $typeKey]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="text-center py-12 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <p class="text-gray-500">No testing overrides configured</p>
        </div>
    @endif
</div>
@endsection
