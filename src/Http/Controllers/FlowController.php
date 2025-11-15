<?php

namespace Jawabapp\RemoteConfig\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jawabapp\RemoteConfig\Models\Flow;

class FlowController extends Controller
{
    /**
     * Display a listing of flows.
     */
    public function index(Request $request): View
    {
        $query = Flow::query()->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        // Filter by default/variants tab - defaults to 'default' tab
        $defaultFilter = $request->input('default', 'default');
        if ($defaultFilter === 'default') {
            $query->where('is_default', true);
        } elseif ($defaultFilter === 'variants') {
            $query->where('is_default', false);
        }

        // Search by ID or content
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $flows = $query->paginate(config('remote-config.admin.per_page', 20));

        $flowTypes = config('remote-config.flow_types', []);

        $stats = [
            'total' => Flow::count(),
            'active' => Flow::where('is_active', true)->count(),
            'inactive' => Flow::where('is_active', false)->count(),
            'default' => Flow::where('is_default', true)->count(),
            'variants' => Flow::where('is_default', false)->count(),
        ];

        return view('remote-config::flow.index', compact('flows', 'flowTypes', 'stats'));
    }

    /**
     * Show the form for creating a new flow.
     */
    public function create(): View
    {
        $flowTypes = config('remote-config.flow_types', []);

        return view('remote-config::flow.create', compact('flowTypes'));
    }

    /**
     * Store a newly created flow.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:' . (config('remote-config.table_prefix', '') . 'flows') . ',name,NULL,id,type,' . $request->input('type')
            ],
            'content' => 'required|json',
        ]);

        $validated['content'] = json_decode($validated['content'], true);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');

        // Check if trying to set as default while inactive
        if ($validated['is_default'] && !$validated['is_active']) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['is_default' => 'A flow cannot be set as default if it is inactive. Please activate the flow first.']);
        }

        // Check if trying to set as default when another default already exists
        if ($validated['is_default']) {
            $existingDefault = Flow::where('type', $validated['type'])
                ->where('is_default', true)
                ->first();

            if ($existingDefault) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['is_default' => "A default flow already exists for type '{$validated['type']}' (Flow #{$existingDefault->id}: {$existingDefault->name}). Please unset it first or don't mark this as default."]);
            }
        }

        $flow = Flow::create($validated);

        return redirect()
            ->route('remote-config.flows.show', $flow)
            ->with('success', 'Flow created successfully');
    }

    /**
     * Display the specified flow.
     */
    public function show(Flow $flow): View
    {
        $flow->load(['logs' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        $experiments = $flow->experiments()->with('assignments')->get();

        $stats = [
            'experiments_count' => $experiments->count(),
            'assignments_count' => $flow->assignments()->count(),
        ];

        return view('remote-config::flow.show', compact('flow', 'experiments', 'stats'));
    }

    /**
     * Show the form for editing the specified flow.
     */
    public function edit(Flow $flow): View
    {
        $flowTypes = config('remote-config.flow_types', []);

        return view('remote-config::flow.edit', compact('flow', 'flowTypes'));
    }

    /**
     * Update the specified flow.
     */
    public function update(Request $request, Flow $flow): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:' . (config('remote-config.table_prefix', '') . 'flows') . ',name,' . $flow->id . ',id,type,' . $request->input('type')
            ],
            'content' => 'required|json',
        ]);

        $validated['content'] = json_decode($validated['content'], true);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');

        // Check if trying to set as default while inactive
        if ($validated['is_default'] && !$validated['is_active']) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['is_default' => 'A flow cannot be set as default if it is inactive. Please activate the flow first.']);
        }

        // Check if trying to set as default when another default already exists
        if ($validated['is_default'] && !$flow->is_default) {
            $existingDefault = Flow::where('type', $validated['type'])
                ->where('is_default', true)
                ->where('id', '!=', $flow->id)
                ->first();

            if ($existingDefault) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['is_default' => "A default flow already exists for type '{$validated['type']}' (Flow #{$existingDefault->id}: {$existingDefault->name}). Please unset it first or don't mark this as default."]);
            }
        }

        $flow->update($validated);

        return redirect()
            ->route('remote-config.flows.show', $flow)
            ->with('success', 'Flow updated successfully');
    }

    /**
     * Remove the specified flow.
     */
    public function destroy(Flow $flow): RedirectResponse
    {
        // Check if flow is being used
        $experimentsCount = $flow->experiments()->count();
        $assignmentsCount = $flow->assignments()->count();

        if ($experimentsCount > 0 || $assignmentsCount > 0) {
            return redirect()
                ->back()
                ->with('error', "Cannot delete flow. It is used by {$experimentsCount} experiments and has {$assignmentsCount} user assignments.");
        }

        $flow->delete();

        return redirect()
            ->route('remote-config.flows.index')
            ->with('success', 'Flow deleted successfully');
    }

    /**
     * Toggle the active status of a flow.
     */
    public function toggle(Flow $flow): RedirectResponse
    {
        // Check if trying to deactivate a default flow
        if ($flow->is_active && $flow->is_default) {
            return redirect()
                ->back()
                ->with('error', 'Cannot deactivate a default flow. Please unset it as default first.');
        }

        $flow->update([
            'is_active' => !$flow->is_active,
        ]);

        $status = $flow->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Flow {$status} successfully");
    }
}
