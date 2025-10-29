<?php

namespace Jawabapp\RemoteConfig\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jawabapp\RemoteConfig\Http\Controllers\Controller;
use Jawabapp\RemoteConfig\Services\ConfigService;
use Jawabapp\RemoteConfig\Models\Confirmation;
use Jawabapp\RemoteConfig\Models\ValidationIssue;

class ConfigController extends Controller
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Get remote configuration with experiments applied.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Get configuration type from request
        $type = $request->input('type', 'default');

        // Get user attributes
        $attributes = [
            'platform' => $request->input('platform'),
            'country' => $request->input('country'),
            'language' => $request->input('language'),
        ];

        // Check for test override (IP-based)
        $testOverrideIp = null;
        if (config('remote-config.testing_enabled', true)) {
            $testOverrideIp = $request->ip();
        }

        // Check for test winner ID
        $testWinnerId = $request->input('test_winner_id');

        // Get configuration
        $config = $this->configService->getConfig(
            $user,
            $type,
            $attributes,
            $testOverrideIp,
            $testWinnerId
        );

        // Get active assignment info (optional)
        $assignment = $this->configService->getOrCreateAssignment($user, $type, $attributes);

        return response()->json([
            'success' => true,
            'data' => $config,
            'meta' => [
                'type' => $type,
                'has_experiment' => $assignment !== null,
                'experiment_id' => $assignment?->experiment_id,
                'flow_id' => $assignment?->flow_id,
            ],
        ]);
    }

    /**
     * Confirm experiment completion.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function confirm(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $request->validate([
            'experiment_name' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        if (!config('remote-config.audit_logging.log_confirmations', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Confirmation tracking is disabled',
            ], 400);
        }

        $confirmation = Confirmation::create([
            'experimentable_type' => get_class($user),
            'experimentable_id' => $user->id,
            'experiment_name' => $request->input('experiment_name'),
            'status' => 'confirmed',
            'metadata' => $request->input('metadata', []),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Experiment confirmed successfully',
            'data' => $confirmation,
        ]);
    }

    /**
     * Report a validation issue.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reportIssue(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $request->validate([
            'path' => 'required|string',
            'invalid_value' => 'required',
            'platform' => 'nullable|string',
            'type' => 'nullable|string',
            'error_message' => 'nullable|string',
        ]);

        $issue = ValidationIssue::logIssue(
            $user,
            $request->input('path'),
            $request->input('invalid_value'),
            $request->input('platform'),
            $request->input('type'),
            $request->input('error_message')
        );

        return response()->json([
            'success' => true,
            'message' => 'Validation issue reported successfully',
            'data' => $issue,
        ]);
    }

    /**
     * Get test flow for QA (IP-based override).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testingFlow(Request $request): JsonResponse
    {
        if (!config('remote-config.testing_enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Testing mode is disabled',
            ], 400);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $type = $request->input('type', 'default');
        $ip = $request->ip();

        // Use same logic as index but force test override
        $attributes = [
            'platform' => $request->input('platform'),
            'country' => $request->input('country'),
            'language' => $request->input('language'),
        ];

        $config = $this->configService->getConfig(
            $user,
            $type,
            $attributes,
            $ip,
            null
        );

        return response()->json([
            'success' => true,
            'data' => $config,
            'meta' => [
                'type' => $type,
                'testing_mode' => true,
                'ip' => $ip,
            ],
        ]);
    }
}
