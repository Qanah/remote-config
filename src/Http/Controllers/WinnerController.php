<?php

namespace Jawabapp\RemoteConfig\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jawabapp\RemoteConfig\Models\Winner;
use Jawabapp\RemoteConfig\Models\Flow;

class WinnerController extends Controller
{
    /**
     * Display a listing of winners.
     */
    public function index(Request $request): View
    {
        $query = Winner::query()->with('flow')->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by platform
        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('country_code', $request->input('country'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $winners = $query->paginate(config('remote-config.admin.per_page', 20));

        $flowTypes = config('remote-config.flow_types', []);
        $platforms = config('remote-config.targeting.platforms', []);
        $countries = config('remote-config.targeting.countries', []);

        $stats = [
            'total' => Winner::count(),
            'active' => Winner::where('is_active', true)->count(),
            'inactive' => Winner::where('is_active', false)->count(),
        ];

        return view('remote-config::winner.index', compact('winners', 'flowTypes', 'platforms', 'countries', 'stats'));
    }

    /**
     * Show the form for creating a new winner.
     */
    public function create(): View
    {
        $flowTypes = config('remote-config.flow_types', []);
        $flows = Flow::where('is_active', true)->get();
        $platforms = config('remote-config.targeting.platforms', []);
        $countries = config('remote-config.targeting.countries', []);
        $languages = config('remote-config.targeting.languages', []);

        return view('remote-config::winner.create', compact(
            'flowTypes',
            'flows',
            'platforms',
            'countries',
            'languages'
        ));
    }

    /**
     * Store a newly created winner.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'platform' => 'required|string|max:255',
            'country_code' => 'required|string|max:2',
            'language' => 'required|string|max:10',
            'flow_id' => 'nullable|exists:' . (config('remote-config.table_prefix', '') . 'flows') . ',id',
            'content' => 'required|json',
        ]);

        $validated['content'] = json_decode($validated['content'], true);
        $validated['is_active'] = $request->has('is_active');

        // Check if winner already exists for this combination
        if (Winner::exists(
            $validated['type'],
            $validated['platform'],
            $validated['country_code'],
            $validated['language']
        )) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'A winner already exists for this platform/country/language combination');
        }

        $winner = Winner::create($validated);

        return redirect()
            ->route('remote-config.winners.show', $winner)
            ->with('success', 'Winner created successfully');
    }

    /**
     * Display the specified winner.
     */
    public function show(Winner $winner): View
    {
        $winner->load([
            'flow',
            'logs' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ]);

        return view('remote-config::winner.show', compact('winner'));
    }

    /**
     * Show the form for editing the specified winner.
     */
    public function edit(Winner $winner): View
    {
        $flowTypes = config('remote-config.flow_types', []);
        $flows = Flow::where('is_active', true)->get();
        $platforms = config('remote-config.targeting.platforms', []);
        $countries = config('remote-config.targeting.countries', []);
        $languages = config('remote-config.targeting.languages', []);

        return view('remote-config::winner.edit', compact(
            'winner',
            'flowTypes',
            'flows',
            'platforms',
            'countries',
            'languages'
        ));
    }

    /**
     * Update the specified winner.
     */
    public function update(Request $request, Winner $winner): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'platform' => 'required|string|max:255',
            'country_code' => 'required|string|max:2',
            'language' => 'required|string|max:10',
            'flow_id' => 'nullable|exists:' . (config('remote-config.table_prefix', '') . 'flows') . ',id',
            'content' => 'required|json',
        ]);

        $validated['content'] = json_decode($validated['content'], true);
        $validated['is_active'] = $request->has('is_active');

        // Check if winner already exists for this combination (excluding current)
        if (Winner::exists(
            $validated['type'],
            $validated['platform'],
            $validated['country_code'],
            $validated['language'],
            $winner->id
        )) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'A winner already exists for this platform/country/language combination');
        }

        $winner->update($validated);

        return redirect()
            ->route('remote-config.winners.show', $winner)
            ->with('success', 'Winner updated successfully');
    }

    /**
     * Remove the specified winner.
     */
    public function destroy(Winner $winner): RedirectResponse
    {
        $winner->delete();

        return redirect()
            ->route('remote-config.winners.index')
            ->with('success', 'Winner deleted successfully');
    }

    /**
     * Toggle the active status of a winner.
     */
    public function toggle(Winner $winner): RedirectResponse
    {
        $winner->update([
            'is_active' => !$winner->is_active,
        ]);

        $status = $winner->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Winner {$status} successfully");
    }
}
