<?php

namespace Jawabapp\RemoteConfig\Services;

use Jawabapp\RemoteConfig\Models\Flow;
use Jawabapp\RemoteConfig\Models\Winner;
use Jawabapp\RemoteConfig\Models\TestOverride;
use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\ExperimentAssignment;
use Jawabapp\RemoteConfig\Models\Confirmation;

class ConfigService
{
    protected ExperimentService $experimentService;

    public function __construct(ExperimentService $experimentService)
    {
        $this->experimentService = $experimentService;
    }

    /**
     * Extract an attribute from experimentable model using configured mapping.
     *
     * @param mixed $experimentable
     * @param string $attributeName
     * @param array $attributes
     * @return mixed
     */
    protected function extractAttribute($experimentable, string $attributeName, array $attributes = [])
    {
        // First check if explicitly provided in attributes array
        if (isset($attributes[$attributeName])) {
            return $attributes[$attributeName];
        }

        // Get attribute mapping from config
        $mapping = config("remote-config.attribute_mapping.{$attributeName}", []);

        // Try each mapped field in order
        foreach ($mapping as $fieldName) {
            $value = $experimentable->getAttribute($fieldName);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get configuration for a user/entity.
     *
     * @param mixed $experimentable The user/entity
     * @param string $type Flow type
     * @param array $attributes User attributes (platform, country, language)
     * @param string|null $testOverrideIp IP for test override
     * @param int|null $testWinnerId Winner ID for testing
     * @return array
     */
    public function getConfig(
        $experimentable,
        string $type,
        array $attributes = [],
        ?string $testOverrideIp = null
    ): array {
        // Get base configuration
        $baseFlow = Flow::getConfig($type);
        $config = $baseFlow?->content ?? [];

        if (!config('remote-config.enabled', true)) {
            return $config;
        }

        // Check for test override first (highest priority)
        if ($testOverrideIp && config('remote-config.testing_enabled', true)) {
            $testOverride = TestOverride::findByIpAndType($testOverrideIp, $type);

            if ($testOverride && isset($testOverride['flow_id'])) {
                $overrideFlow = Flow::find($testOverride['flow_id']);
                if ($overrideFlow && $overrideFlow->content) {
                    return array_replace_recursive($config, $overrideFlow->content);
                }
            }
        }

        // Check for winner configuration (second priority)
        $platform = $this->extractAttribute($experimentable, 'platform', $attributes);
        $country = $this->extractAttribute($experimentable, 'country', $attributes);
        $language = $this->extractAttribute($experimentable, 'language', $attributes);

        if ($platform && $country && $language) {

            $winner = Winner::getWinner($type, $platform, $country, $language);

            if ($winner) {
                $config = array_replace_recursive($config, $winner->content);
            }
        }

        // Check for active experiment (third priority)
        $assignment = $this->getOrCreateAssignment(
            $experimentable,
            $type,
            $attributes
        );

        if ($assignment && $assignment->flow) {
            return array_replace_recursive($config, $assignment->flow->content);
        }

        // Return base configuration
        return $config;
    }

    /**
     * Get or create experiment assignment for a user.
     *
     * @param mixed $experimentable
     * @param string $type
     * @param array $attributes
     * @return ExperimentAssignment|null
     */
    public function getOrCreateAssignment(
        $experimentable,
        string $type,
        array $attributes = []
    ): ?ExperimentAssignment {
        // Check for existing assignment
        $existing = ExperimentAssignment::where('experimentable_type', get_class($experimentable))
            ->where('experimentable_id', $experimentable->id)
            ->whereHas('experiment', function ($query) use ($type) {
                $query->where('type', $type)->where('is_active', true);
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        // Get user attributes
        $platform = $this->extractAttribute($experimentable, 'platform', $attributes);
        $country = $this->extractAttribute($experimentable, 'country', $attributes);
        $language = $this->extractAttribute($experimentable, 'language', $attributes);

        if (!$platform || !$country || !$language) {
            return null;
        }

        // Check if user created after experiment start date
        $userCreatedAfter = config('remote-config.user_created_after_date');
        if ($userCreatedAfter && $experimentable->created_at && $experimentable->created_at->isBefore($userCreatedAfter)) {
            return null;
        }

        // Find matching active experiment
        $experiment = Experiment::getActiveExperiment(
            $type,
            $platform,
            $country,
            $language
        );

        if (!$experiment || $experiment->flows->isEmpty()) {
            return null;
        }

        // Select a flow variant
        $selectedFlow = $this->experimentService->selectFlow($experiment);

        if (!$selectedFlow) {
            return null;
        }

        // Create assignment
        return ExperimentAssignment::create([
            'experimentable_type' => get_class($experimentable),
            'experimentable_id' => $experimentable->id,
            'experiment_id' => $experiment->id,
            'flow_id' => $selectedFlow->id,
        ]);
    }

    /**
     * Get assignment stats for an experiment.
     *
     * @param Experiment $experiment
     * @return array
     */
    public function getAssignmentStats(Experiment $experiment): array
    {
        $assignments = ExperimentAssignment::where('experiment_id', $experiment->id)->get();

        $stats = [];
        $byFlow = [];
        $total = $assignments->count();

        foreach ($experiment->flows as $flow) {
            $count = $assignments->where('flow_id', $flow->id)->count();
            $percentage = $total > 0 ? ($count / $total) * 100 : 0;

            $stats[] = [
                'flow_id' => $flow->id,
                'flow_type' => $flow->type,
                'assigned_count' => $count,
                'percentage' => round($percentage, 2),
            ];

            $byFlow[$flow->id] = $count;
        }

        return [
            'total_assignments' => $total,
            'flows' => $stats,
            'by_flow' => $byFlow,
        ];
    }

}
