<?php

namespace Jawabapp\RemoteConfig\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jawabapp\RemoteConfig\Models\TestOverride;
use Jawabapp\RemoteConfig\Models\Flow;

class TestingController extends Controller
{
    /**
     * Display a listing of test overrides.
     */
    public function index(Request $request): View
    {
        if (!config('remote-config.testing_enabled', true)) {
            abort(403, 'Testing mode is disabled');
        }

        $flowTypes = config('remote-config.flow_types', []);
        $flows = Flow::where('is_default', true)->get();

        // Get all test overrides grouped by type
        $overridesByType = [];
        foreach ($flowTypes as $typeKey => $typeName) {
            $overrides = TestOverride::getAllForType($typeKey);
            if (!empty($overrides)) {
                $overridesByType[$typeKey] = [
                    'name' => $typeName,
                    'overrides' => $overrides,
                ];
            }
        }

        $stats = [
            'total' => collect($overridesByType)->sum(fn($type) => count($type['overrides'])),
            'types' => count($overridesByType),
        ];

        return view('remote-config::testing.index', compact('flowTypes', 'flows', 'overridesByType', 'stats'));
    }

    /**
     * Store a new test override.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!config('remote-config.testing_enabled', true)) {
            abort(403, 'Testing mode is disabled');
        }

        $validated = $request->validate([
            'ip' => 'required|ip',
            'type' => 'required|string',
            'flow_id' => 'required|exists:' . (config('remote-config.table_prefix', '') . 'flows') . ',id',
            'ttl' => 'nullable|integer|min:60|max:604800', // 1 minute to 7 days
        ]);

        $testOverride = new TestOverride($validated['ip'], $validated['type']);

        $ttl = $validated['ttl'] ?? config('remote-config.cache_ttl', 604800);

        $success = $testOverride->set($validated['flow_id'], $ttl);

        if ($success) {
            return redirect()
                ->back()
                ->with('success', 'Test override created successfully');
        }

        return redirect()
            ->back()
            ->with('error', 'Failed to create test override. Check if Redis is configured.');
    }

    /**
     * Remove a test override.
     */
    public function destroy(string $ip, string $type): RedirectResponse
    {
        if (!config('remote-config.testing_enabled', true)) {
            abort(403, 'Testing mode is disabled');
        }

        // Decode IP (it might be encoded in the URL)
        $ip = str_replace('_', '.', $ip);

        $testOverride = new TestOverride($ip, $type);
        $success = $testOverride->delete();

        if ($success) {
            return redirect()
                ->back()
                ->with('success', 'Test override deleted successfully');
        }

        return redirect()
            ->back()
            ->with('error', 'Test override not found or already deleted');
    }
}
