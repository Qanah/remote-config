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
            'content' => 'required|json',
            'overwrite_id' => 'nullable|integer',
        ]);

        $validated['content'] = json_decode($validated['content'], true);
        $validated['is_active'] = $request->has('is_active');

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
            'winners_count' => $flow->winners()->count(),
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
            'content' => 'required|json',
            'overwrite_id' => 'nullable|integer',
        ]);

        $validated['content'] = json_decode($validated['content'], true);
        $validated['is_active'] = $request->has('is_active');

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
        $winnersCount = $flow->winners()->count();

        if ($experimentsCount > 0 || $winnersCount > 0) {
            return redirect()
                ->back()
                ->with('error', "Cannot delete flow. It is used by {$experimentsCount} experiments and {$winnersCount} winners.");
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
        $flow->update([
            'is_active' => !$flow->is_active,
        ]);

        $status = $flow->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Flow {$status} successfully");
    }
}
