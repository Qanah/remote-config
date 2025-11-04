@extends('remote-config::layouts.app')

@section('page-title', 'Edit Experiment')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900">
                Edit Experiment
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Update experiment settings and targeting
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('remote-config.experiments.show', $experiment) }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('remote-config.experiments.update', $experiment) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <div class="px-4 py-6 sm:p-8">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Experiment Name</label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" value="{{ old('name', $experiment->name) }}" required class="block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <div class="mt-1">
                            <select id="type" name="type" required class="block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                                <option value="">Select type</option>
                                @foreach($flowTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('type', $experiment->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
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
                                    <input id="platform-{{ $key }}" name="platforms[]" value="{{ $key }}" type="checkbox" {{ in_array($key, old('platforms', $experiment->platforms)) ? 'checked' : '' }} class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600 focus:ring-primary-600">
                                    <label for="platform-{{ $key }}" class="ml-3 text-sm leading-6 text-gray-600">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('platforms')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-full" x-data="{ allCountries: false }">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Countries</label>
                            <label class="flex items-center text-sm">
                                <input type="checkbox" x-model="allCountries" @change="document.querySelectorAll('input[name=\'countries[]\']').forEach(el => el.checked = allCountries)" class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600 focus:ring-primary-600">
                                <span class="ml-2 text-gray-600">Select All</span>
                            </label>
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-4">
                            @foreach($countries as $key => $label)
                                <div class="flex items-center">
                                    <input id="country-{{ $key }}" name="countries[]" value="{{ $key }}" type="checkbox" {{ in_array($key, old('countries', $experiment->countries)) ? 'checked' : '' }} class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600 focus:ring-primary-600">
                                    <label for="country-{{ $key }}" class="ml-3 text-sm leading-6 text-gray-600">{{ $label }}</label>
                                </div>
            @endforeach
                        </div>
                        @error('countries')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-full" x-data="{ allLanguages: false }">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Languages</label>
                            <label class="flex items-center text-sm">
                                <input type="checkbox" x-model="allLanguages" @change="document.querySelectorAll('input[name=\'languages[]\']').forEach(el => el.checked = allLanguages)" class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600 focus:ring-primary-600">
                                <span class="ml-2 text-gray-600">Select All</span>
                            </label>
                        </div>
                        <div class="mt-2 space-y-2">
                            @foreach($languages as $key => $label)
                                <div class="flex items-center">
                                    <input id="language-{{ $key }}" name="languages[]" value="{{ $key }}" type="checkbox" {{ in_array($key, old('languages', $experiment->languages)) ? 'checked' : '' }} class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600 focus:ring-primary-600">
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
                            <input type="date" name="user_created_after_date" id="user_created_after_date" value="{{ old('user_created_after_date', $experiment->user_created_after_date?->format('Y-m-d')) }}" class="block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Only users created after this date will be included</p>
                    </div>

                    <div class="sm:col-span-full" x-data="{
                        flows: {{ json_encode($experiment->flows->map(fn($f, $i) => ['id' => $f->id, 'ratio' => old('flows.' . $i . '.ratio', $f->pivot->ratio)])->values()) }},
                        get total() {
                            return this.flows.reduce((sum, flow) => sum + parseInt(flow.ratio || 0), 0);
                        },
                        get isValid() {
                            return this.total === 100;
                        }
                    }">
                        <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Experiment Variants & Ratios</label>
                        <div class="mb-3 p-3 rounded-lg border" :class="isValid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium" :class="isValid ? 'text-green-800' : 'text-red-800'">
                                    Total: <span x-text="total"></span>%
                                </span>
                                <span class="text-xs" :class="isValid ? 'text-green-600' : 'text-red-600'" x-text="isValid ? '✓ Valid' : '✗ Must equal 100%'"></span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            @foreach($experiment->flows as $index => $flow)
                                <div class="flex items-center gap-4 bg-gray-50 rounded-lg p-4">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $flow->display_label }}
                                        </div>
                                        <input type="hidden" name="flows[{{ $index }}][id]" value="{{ $flow->id }}">
                                    </div>
                                    <div class="w-32">
                                        <label for="flow_ratio_{{ $flow->id }}" class="block text-xs font-medium text-gray-700 mb-1">Ratio %</label>
                                        <input type="number"
                                               id="flow_ratio_{{ $flow->id }}"
                                               name="flows[{{ $index }}][ratio]"
                                               x-model="flows[{{ $index }}].ratio"
                                               required
                                               min="1"
                                               max="100"
                                               class="block w-full rounded-md border-2 border-gray-400 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-3 py-2">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Adjust the distribution ratios. Total must equal 100%.</p>
                        @error('flows')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-full">
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" {{ old('is_active', $experiment->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-2 border-gray-400 text-primary-600 focus:ring-primary-600">
                            <label for="is_active" class="ml-3 text-sm leading-6 text-gray-900">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <a href="{{ route('remote-config.experiments.show', $experiment) }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Update Experiment</button>
            </div>
        </div>
    </form>

    <!-- Delete Form -->
    <div class="bg-white shadow-sm ring-1 ring-red-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <h3 class="text-base font-semibold leading-6 text-red-900">Danger Zone</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Once you delete an experiment, all assignment data will be lost. This action cannot be undone.</p>
            </div>
            <form action="{{ route('remote-config.experiments.destroy', $experiment) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this experiment? This action cannot be undone.');" class="mt-5">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Delete Experiment
                </button>
            </form>
        </div>
    </div>
</div>
@endsection