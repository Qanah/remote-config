<?php

namespace Jawabapp\RemoteConfig\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\Flow;
use Jawabapp\RemoteConfig\Services\ExperimentService;
use Jawabapp\RemoteConfig\Services\ConfigService;

class ExperimentController extends Controller
{
    protected ExperimentService $experimentService;
    protected ConfigService $configService;

    public function __construct(ExperimentService $experimentService, ConfigService $configService)
    {
        $this->experimentService = $experimentService;
        $this->configService = $configService;
    }

    /**
     * Display a listing of experiments.
     */
    public function index(Request $request): View
    {
        $query = Experiment::query()->with('flows')->orderBy('created_at', 'desc');
        $isSqlite = config('database.default') === 'sqlite';

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        // Filter by platform
        if ($request->filled('platform')) {
            $platform = $request->input('platform');
            if ($isSqlite) {
                $query->where('platforms', 'LIKE', '%"' . $platform . '"%');
            } else {
                $query->whereRaw("JSON_SEARCH(platforms, 'one', ?) is not null", [$platform]);
            }
        }

        // Filter by country
        if ($request->filled('country')) {
            $country = $request->input('country');
            if ($isSqlite) {
                $query->where('countries', 'LIKE', '%"' . $country . '"%');
            } else {
                $query->whereRaw("JSON_SEARCH(countries, 'one', ?) is not null", [$country]);
            }
        }

        // Filter by language
        if ($request->filled('language')) {
            $language = $request->input('language');
            if ($isSqlite) {
                $query->where('languages', 'LIKE', '%"' . $language . '"%');
            } else {
                $query->whereRaw("JSON_SEARCH(languages, 'one', ?) is not null", [$language]);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        $experiments = $query->paginate(config('remote-config.admin.per_page', 20));

        $flowTypes = config('remote-config.flow_types', []);
        $platforms = config('remote-config.targeting.platforms', []);
        $countries = config('remote-config.targeting.countries', []);
        $languages = config('remote-config.targeting.languages', []);

        $stats = [
            'total' => Experiment::count(),
            'active' => Experiment::where('is_active', true)->count(),
            'inactive' => Experiment::where('is_active', false)->count(),
        ];

        return view('remote-config::experiment.index', compact('experiments', 'flowTypes', 'platforms', 'countries', 'languages', 'stats'));
    }

    /**
     * Show the form for creating a new experiment.
     */
    public function create(): View
    {
        $flowTypes = config('remote-config.flow_types', []);
        $flows = Flow::where('is_active', true)->get();
        $platforms = config('remote-config.targeting.platforms', []);
        $countries = config('remote-config.targeting.countries', []);
        $languages = config('remote-config.targeting.languages', []);

        return view('remote-config::experiment.create', compact(
            'flowTypes',
            'flows',
            'platforms',
            'countries',
            'languages'
        ));
    }

    /**
     * Store a newly created experiment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'platforms' => 'required|array|min:1',
            'countries' => 'required|array|min:1',
            'languages' => 'required|array|min:1',
            'user_created_after_date' => 'nullable|date',
            'flows' => 'required|array|min:2',
            'flows.*.id' => 'required|exists:' . (config('remote-config.table_prefix', '') . 'flows') . ',id',
            'flows.*.ratio' => 'required|integer|min:1|max:100',
        ]);

        // Validate that ratios add up to 100%
        $totalRatio = array_sum(array_column($validated['flows'], 'ratio'));
        if ($totalRatio !== 100) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['flows' => "The total of all ratios must equal 100%. Current total: {$totalRatio}%"]);
        }

        $validated['is_active'] = $request->has('is_active');

        // Check for conflicts with overlapping targeting
        $experiment = new Experiment($validated);
        if (config('remote-config.validation.prevent_overlapping_experiments', true) && $experiment->hasConflict()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'type' => 'An active experiment already exists with overlapping platforms, countries, and languages for type: ' . $validated['type']
                ]);
        }

        $experiment = Experiment::create($validated);

        // Attach flows with ratios
        foreach ($validated['flows'] as $flowData) {
            $experiment->flows()->attach($flowData['id'], ['ratio' => $flowData['ratio']]);
        }

        return redirect()
            ->route('remote-config.experiments.show', $experiment)
            ->with('success', 'Experiment created successfully');
    }

    /**
     * Display the specified experiment.
     */
    public function show(Experiment $experiment): View
    {
        $experiment->load([
            'flows',
            'assignments.experimentable',
            'logs' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ]);

        // Get statistics
        $assignmentStats = $this->configService->getAssignmentStats($experiment);
        $experimentStats = $this->experimentService->getExperimentStats($experiment);

        $stats = [
            'total_assignments' => $assignmentStats['total_assignments'],
            'total_selections' => $experimentStats['total'],
            'confirmations' => $experiment->confirmations()->count(),
        ];

        return view('remote-config::experiment.show', compact('experiment', 'assignmentStats', 'experimentStats', 'stats'));
    }

    /**
     * Show the form for editing the specified experiment.
     */
    public function edit(Experiment $experiment): View
    {
        $experiment->load('flows');
        $flowTypes = config('remote-config.flow_types', []);
        $flows = Flow::where('is_active', true)->get();
        $platforms = config('remote-config.targeting.platforms', []);
        $countries = config('remote-config.targeting.countries', []);
        $languages = config('remote-config.targeting.languages', []);

        return view('remote-config::experiment.edit', compact(
            'experiment',
            'flowTypes',
            'flows',
            'platforms',
            'countries',
            'languages'
        ));
    }

    /**
     * Update the specified experiment.
     */
    public function update(Request $request, Experiment $experiment): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'platforms' => 'required|array|min:1',
            'countries' => 'required|array|min:1',
            'languages' => 'required|array|min:1',
            'user_created_after_date' => 'nullable|date',
            'flows' => 'required|array|min:2',
            'flows.*.id' => 'required|exists:' . (config('remote-config.table_prefix', '') . 'flows') . ',id',
            'flows.*.ratio' => 'required|integer|min:1|max:100',
        ]);

        // Validate that ratios add up to 100%
        $totalRatio = array_sum(array_column($validated['flows'], 'ratio'));
        if ($totalRatio !== 100) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['flows' => "The total of all ratios must equal 100%. Current total: {$totalRatio}%"]);
        }

        $validated['is_active'] = $request->has('is_active');

        // Check for conflicts with overlapping targeting
        $experiment->fill($validated);
        if (config('remote-config.validation.prevent_overlapping_experiments', true) && $experiment->hasConflict()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'type' => 'An active experiment already exists with overlapping platforms, countries, and languages for type: ' . $validated['type']
                ]);
        }

        $experiment->update($validated);

        // Update flow ratios in pivot table
        $flowData = [];
        foreach ($validated['flows'] as $flowItem) {
            $flowData[$flowItem['id']] = ['ratio' => $flowItem['ratio']];
        }
        $experiment->flows()->sync($flowData);

        return redirect()
            ->route('remote-config.experiments.show', $experiment)
            ->with('success', 'Experiment updated successfully');
    }

    /**
     * Remove the specified experiment.
     */
    public function destroy(Experiment $experiment): RedirectResponse
    {
        $assignmentsCount = $experiment->assignments()->count();

        if ($assignmentsCount > 0) {
            return redirect()
                ->back()
                ->with('warning', "Experiment has {$assignmentsCount} user assignments. Consider deactivating instead of deleting.");
        }

        $experiment->delete();

        return redirect()
            ->route('remote-config.experiments.index')
            ->with('success', 'Experiment deleted successfully');
    }

    /**
     * Toggle the active status of an experiment.
     */
    public function toggle(Experiment $experiment): RedirectResponse
    {
        $experiment->update([
            'is_active' => !$experiment->is_active,
        ]);

        $status = $experiment->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Experiment {$status} successfully");
    }

    /**
     * Attach a flow to an experiment.
     */
    public function attachFlow(Request $request, Experiment $experiment): JsonResponse
    {
        $validated = $request->validate([
            'flow_id' => 'required|exists:' . (config('remote-config.table_prefix', '') . 'flows') . ',id',
            'ratio' => 'required|integer|min:1|max:100',
        ]);

        if ($experiment->flows()->where('flow_id', $validated['flow_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Flow is already attached to this experiment',
            ], 400);
        }

        $experiment->flows()->attach($validated['flow_id'], ['ratio' => $validated['ratio']]);

        return response()->json([
            'success' => true,
            'message' => 'Flow attached successfully',
        ]);
    }

    /**
     * Detach a flow from an experiment.
     */
    public function detachFlow(Experiment $experiment, Flow $flow): JsonResponse
    {
        if ($experiment->flows()->count() <= 2) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove flow. Experiment must have at least 2 flows',
            ], 400);
        }

        $experiment->flows()->detach($flow->id);

        return response()->json([
            'success' => true,
            'message' => 'Flow detached successfully',
        ]);
    }

    /**
     * Get experiment statistics.
     */
    public function stats(Experiment $experiment): JsonResponse
    {
        $assignmentStats = $this->configService->getAssignmentStats($experiment);
        $experimentStats = $this->experimentService->getExperimentStats($experiment);

        return response()->json([
            'success' => true,
            'data' => [
                'assignments' => $assignmentStats,
                'selections' => $experimentStats,
            ],
        ]);
    }
}
