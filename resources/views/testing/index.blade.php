@extends('remote-config::layouts.app')

@section('page-title', 'Testing Overrides')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900">Testing Overrides</h2>
            <p class="mt-1 text-sm text-gray-500">Override config for specific IP addresses</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <form action="{{ route('remote-config.testing.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all test overrides?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Clear All
                </button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('remote-config.testing.store') }}"
          class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl"
          x-data="{
              selectedType: '{{ array_key_first($flowTypes) }}',
              availableFlows: {{ json_encode($flows->map(fn($f) => ['id' => $f->id, 'type' => $f->type, 'name' => $f->name, 'display_label' => $f->display_label])->values()) }},
              get filteredFlows() {
                  return this.availableFlows.filter(f => f.type === this.selectedType);
              }
          }">
        @csrf
        <div class="px-4 py-6 sm:p-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-4 items-end">
                <div>
                    <label for="ip" class="block text-sm font-medium text-gray-900">IP Address</label>
                    <input type="text" name="ip" id="ip" required placeholder="192.168.1.1" class="mt-2 block w-full rounded-md border-2 border-gray-400 text-gray-900 shadow-sm text-base px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-900">Type</label>
                    <select id="type" name="type" x-model="selectedType" required class="mt-2 block w-full rounded-md border-2 border-gray-400 text-gray-900 shadow-sm text-base px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
                        @foreach($flowTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="flow_id" class="block text-sm font-medium text-gray-900">Flow</label>
                    <select id="flow_id" name="flow_id" required class="mt-2 block w-full rounded-md border-2 border-gray-400 text-gray-900 shadow-sm text-base px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
                        <template x-if="filteredFlows.length === 0">
                            <option value="">No flows available for this type</option>
                        </template>
                        <template x-for="flow in filteredFlows" :key="flow.id">
                            <option :value="flow.id" x-text="flow.display_label"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <button type="submit" class="mt-8 w-full rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                        Add Override
                    </button>
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
                    @foreach($typeData['overrides'] as $override)
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-gray-900">{{ $override['ip'] }}</span>
                            <span class="text-sm text-gray-500 ml-4">→
                                @if($override['flow'])
                                    {{ $override['flow']->display_label }}
                                @else
                                    Flow #{{ $override['flow_id'] }} (deleted)
                                @endif
                            </span>
                        </div>
                        <form action="{{ route('remote-config.testing.destroy', [str_replace('.', '_', $override['ip']), $typeKey]) }}" method="POST">
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
