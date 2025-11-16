<?php

namespace Jawabapp\RemoteConfig\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jawabapp\RemoteConfig\Http\Controllers\Controller;
use Jawabapp\RemoteConfig\Services\ConfigService;
use Jawabapp\RemoteConfig\Models\Confirmation;
use Jawabapp\RemoteConfig\Models\ValidationIssue;
use Jawabapp\RemoteConfig\Models\ExperimentAssignment;

class ConfigController extends Controller
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Get remote configuration with experiments applied.
     * Supports single type or multiple types in one request.
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

        // Validate fields - type can be string, array, or null
        $validated = $request->validate([
            'type' => 'nullable',
            'type.*' => 'string|max:255',
            'platform' => 'nullable|string',
            'country' => 'nullable|string',
            'language' => 'nullable|string',
        ]);

        // Normalize type parameter
        $typeInput = $request->input('type');
        $types = [];

        if ($typeInput === null || $typeInput === '') {
            // No type specified - get all active types
            $types = $this->configService->getActiveTypes();
        } elseif (is_array($typeInput)) {
            // Array of types provided
            $types = $typeInput;
        } elseif (is_string($typeInput)) {
            // Single type as string - convert to array for consistent processing
            $types = [$typeInput];
        }

        // Limit number of types per request (prevent abuse)
        $maxTypes = config('remote-config.api.max_types_per_request', 10);
        if (count($types) > $maxTypes) {
            return response()->json([
                'success' => false,
                'message' => "Maximum {$maxTypes} types allowed per request",
            ], 400);
        }

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

        // Get configurations using the batch method (handles both single and multiple)
        $result = $this->configService->getMultipleConfigs(
            $user,
            $types,
            $attributes,
            $testOverrideIp
        );

        // Return grouped format for both single and multiple types
        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    /**
     * Confirm experiment completion.
     * User can only confirm experiments they are assigned to.
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

        $validated = $request->validate([
            'experiment_id' => 'required|integer',
            'flow_id' => 'required|integer',
            'metadata' => 'nullable|array',
        ]);

        if (!config('remote-config.audit_logging.log_confirmations', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Confirmation tracking is disabled',
            ], 400);
        }

        try {
            // Validate that user is assigned to this experiment with this flow
            $assignment = ExperimentAssignment::where('experimentable_type', get_class($user))
                ->where('experimentable_id', $user->id)
                ->where('experiment_id', $validated['experiment_id'])
                ->where('flow_id', $validated['flow_id'])
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not assigned to this experiment with the specified flow.',
                ], 400);
            }

            // Check if already confirmed
            $existingConfirmation = Confirmation::where('experimentable_type', get_class($user))
                ->where('experimentable_id', $user->id)
                ->where('experiment_id', $validated['experiment_id'])
                ->where('status', 'confirmed')
                ->first();

            if ($existingConfirmation) {
                return response()->json([
                    'success' => true,
                    'message' => 'Experiment already confirmed',
                    'data' => $existingConfirmation,
                ]);
            }

            // Create confirmation
            $confirmation = Confirmation::create([
                'experimentable_type' => get_class($user),
                'experimentable_id' => $user->id,
                'experiment_id' => $validated['experiment_id'],
                'flow_id' => $validated['flow_id'],
                'status' => 'confirmed',
                'metadata' => $validated['metadata'] ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Experiment confirmed successfully',
                'data' => $confirmation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Report validation issue(s).
     * Accepts both single issue and multiple issues in one request.
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

        // Check if request body is an array (multiple issues) or object (single issue)
        $input = $request->all();
        $isBulk = isset($input[0]) && is_array($input[0]);

        if ($isBulk) {
            // Validate multiple issues
            $validated = $request->validate([
                '*.path' => 'required|string',
                '*.invalid_value' => 'nullable',
                '*.platform' => 'nullable|string',
                '*.type' => 'nullable|string',
                '*.error_message' => 'nullable|string',
            ]);

            // Log each issue
            $issues = [];
            foreach ($validated as $issueData) {
                $issues[] = ValidationIssue::logIssue(
                    $user,
                    $issueData['path'],
                    $issueData['invalid_value'],
                    $issueData['platform'] ?? null,
                    $issueData['type'] ?? null,
                    $issueData['error_message'] ?? null
                );
            }

            return response()->json([
                'success' => true,
                'message' => count($issues) . ' validation issues reported successfully',
                'data' => $issues,
            ]);
        } else {
            // Validate single issue
            $validated = $request->validate([
                'path' => 'required|string',
                'invalid_value' => 'nullable',
                'platform' => 'nullable|string',
                'type' => 'nullable|string',
                'error_message' => 'nullable|string',
            ]);

            $issue = ValidationIssue::logIssue(
                $user,
                $validated['path'],
                $validated['invalid_value'],
                $validated['platform'] ?? null,
                $validated['type'] ?? null,
                $validated['error_message'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Validation issue reported successfully',
                'data' => $issue,
            ]);
        }
    }

}
