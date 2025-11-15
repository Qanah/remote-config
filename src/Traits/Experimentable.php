<?php

namespace Jawabapp\RemoteConfig\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\ExperimentAssignment;
use Jawabapp\RemoteConfig\Models\Confirmation;
use Jawabapp\RemoteConfig\Models\Winner;
use Jawabapp\RemoteConfig\Services\ExperimentService;

trait Experimentable
{
    /**
     * Get all experiment assignments for this user.
     */
    public function experimentAssignments(): MorphMany
    {
        return $this->morphMany(ExperimentAssignment::class, 'experimentable');
    }

    /**
     * Get all experiment confirmations for this user.
     */
    public function experimentConfirmations(): MorphMany
    {
        return $this->morphMany(Confirmation::class, 'experimentable');
    }

    /**
     * Get the active experiment assignment for a specific type.
     */
    public function getExperimentAssignment(string $type): ?ExperimentAssignment
    {
        return $this->experimentAssignments()
            ->whereHas('experiment', function ($query) use ($type) {
                $query->where('type', $type)->where('is_active', true);
            })
            ->first();
    }

    /**
     * Get or create experiment assignment for this user.
     *
     * @param string $type Flow type
     * @param int|null $overwriteId Experiment overwrite ID
     * @param array $userAttributes User attributes (platform, country, language)
     * @return ExperimentAssignment|null
     */
    public function assignToExperiment(
        string $type,
        ?int $overwriteId = null,
        array $userAttributes = []
    ): ?ExperimentAssignment {
        // Check if already assigned
        $existing = $this->getExperimentAssignment($type);
        if ($existing) {
            return $existing;
        }

        // Get user attributes
        $platform = $userAttributes['platform'] ?? $this->getAttribute('platform') ?? $this->getAttribute('os');
        $country = $userAttributes['country'] ?? $this->getAttribute('country_code') ?? $this->getAttribute('geo_country_code');
        $language = $userAttributes['language'] ?? $this->getAttribute('language') ?? $this->getAttribute('lang');

        if (!$platform || !$country || !$language) {
            return null;
        }

        // Check if user should be in experiments
        $userCreatedAfter = config('remote-config.user_created_after_date');
        if ($userCreatedAfter && $this->created_at->isBefore($userCreatedAfter)) {
            return null;
        }

        // Find active experiment
        $experiment = Experiment::getActiveExperiment($type, $overwriteId, $platform, $country, $language);

        if (!$experiment || $experiment->flows->isEmpty()) {
            return null;
        }

        // Use ExperimentService to select a flow
        $experimentService = app(ExperimentService::class);
        $selectedFlow = $experimentService->selectFlow($experiment);

        if (!$selectedFlow) {
            return null;
        }

        // Create assignment
        return ExperimentAssignment::create([
            'experimentable_type' => get_class($this),
            'experimentable_id' => $this->id,
            'experiment_id' => $experiment->id,
            'flow_id' => $selectedFlow->id,
        ]);
    }

    /**
     * Apply winner configuration if available.
     *
     * @param array $baseConfig Base configuration
     * @param string $type Flow type
     * @param array $userAttributes User attributes
     * @param int|null $testWinnerId Test winner ID for preview
     * @return array Modified configuration
     */
    public function applyWinnerConfig(
        array $baseConfig,
        string $type,
        array $userAttributes = [],
        ?int $testWinnerId = null
    ): array {
        $platform = $userAttributes['platform'] ?? $this->getAttribute('platform') ?? $this->getAttribute('os');
        $country = $userAttributes['country'] ?? $this->getAttribute('country_code') ?? $this->getAttribute('geo_country_code');
        $language = $userAttributes['language'] ?? $this->getAttribute('language') ?? $this->getAttribute('lang');

        if (!$platform || !$country || !$language) {
            return $baseConfig;
        }

        // Get winner
        if ($testWinnerId) {
            $winner = Winner::find($testWinnerId);
        } else {
            $winner = Winner::getWinner($type, $platform, $country, $language);
        }

        if (!$winner) {
            return $baseConfig;
        }

        // Merge winner configuration with base
        return array_replace_recursive($baseConfig, $winner->content);
    }

    /**
     * Confirm an experiment.
     * User can only confirm experiments they are assigned to.
     *
     * @param string $experimentName
     * @param array $metadata
     * @return Confirmation|null
     * @throws \Exception
     */
    public function confirmExperiment(string $experimentName, array $metadata = []): ?Confirmation
    {
        $experiment = Experiment::where('name', $experimentName)->first();

        if (!$experiment) {
            throw new \Exception("Experiment '{$experimentName}' not found.");
        }

        // Check if user has an assignment for this experiment
        $assignment = ExperimentAssignment::where('experimentable_type', get_class($this))
            ->where('experimentable_id', $this->id)
            ->where('experiment_id', $experiment->id)
            ->first();

        if (!$assignment) {
            throw new \Exception("User is not assigned to experiment '{$experimentName}'. Cannot confirm unassigned experiment.");
        }

        // Check if already confirmed
        $existingConfirmation = Confirmation::where('experimentable_type', get_class($this))
            ->where('experimentable_id', $this->id)
            ->where('experiment_id', $experiment->id)
            ->where('status', 'confirmed')
            ->first();

        if ($existingConfirmation) {
            return $existingConfirmation; // Already confirmed, return existing
        }

        // Create confirmation with assignment details
        return Confirmation::create([
            'experimentable_type' => get_class($this),
            'experimentable_id' => $this->id,
            'experiment_id' => $experiment->id,
            'experiment_name' => $experimentName,
            'status' => 'confirmed',
            'metadata' => array_merge([
                'flow_id' => $assignment->flow_id,
                'confirmed_at' => now()->toDateTimeString(),
            ], $metadata),
        ]);
    }

    /**
     * Check if user has confirmed an experiment.
     *
     * @param string $experimentName
     * @return bool
     */
    public function hasConfirmedExperiment(string $experimentName): bool
    {
        return Confirmation::hasConfirmed($this, $experimentName);
    }
}
