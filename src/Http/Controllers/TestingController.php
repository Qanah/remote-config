<?php

namespace Jawabapp\RemoteConfig\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
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
        $flows = Flow::all();

        // Create a flow lookup array for easy access
        $flowsById = $flows->keyBy('id');

        // Get all test overrides grouped by type
        $overridesByType = [];
        foreach ($flowTypes as $typeKey => $typeName) {
            $overrides = TestOverride::getAllForType($typeKey);
            if (!empty($overrides)) {
                // Enhance overrides with flow details
                $enrichedOverrides = [];
                foreach ($overrides as $ip => $flowId) {
                    $flow = $flowsById->get($flowId);
                    $enrichedOverrides[] = [
                        'ip' => $ip,
                        'flow_id' => $flowId,
                        'flow' => $flow,
                    ];
                }

                $overridesByType[$typeKey] = [
                    'name' => $typeName,
                    'overrides' => $enrichedOverrides,
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
        ]);

        // Check if override already exists for this IP and type
        if (TestOverride::exists($validated['ip'], $validated['type'])) {
            throw ValidationException::withMessages([
                'ip' => 'A testing override already exists for this IP and type combination.'
            ]);
        }

        // Create the test override
        TestOverride::create([
            'ip' => $validated['ip'],
            'type' => $validated['type'],
            'flow_id' => $validated['flow_id'],
        ]);

        return redirect()
            ->back()
            ->with('success', 'Test override created successfully');
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

        $success = TestOverride::deleteByIpAndType($ip, $type);

        if ($success) {
            return redirect()
                ->back()
                ->with('success', 'Test override deleted successfully');
        }

        return redirect()
            ->back()
            ->with('error', 'Test override not found or already deleted');
    }

    /**
     * Clear all test overrides.
     */
    public function clear(): RedirectResponse
    {
        if (!config('remote-config.testing_enabled', true)) {
            abort(403, 'Testing mode is disabled');
        }

        TestOverride::clear();

        return redirect()
            ->back()
            ->with('success', 'All test overrides cleared successfully');
    }
}