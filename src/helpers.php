<?php

use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\ExperimentAssignment;
use Jawabapp\RemoteConfig\Services\ConfigService;
use Jawabapp\RemoteConfig\Services\ExperimentService;

if (!function_exists('remote_config')) {
    /**
     * Get a remote configuration value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function remote_config(?string $key = null, $default = null)
    {
        if ($key === null) {
            return config('remote-config');
        }

        return config("remote-config.{$key}", $default);
    }
}

if (!function_exists('experiment_service')) {
    /**
     * Get the ExperimentService instance.
     *
     * @return ExperimentService
     */
    function experiment_service(): ExperimentService
    {
        return app(ExperimentService::class);
    }
}

if (!function_exists('config_service')) {
    /**
     * Get the ConfigService instance.
     *
     * @return ConfigService
     */
    function config_service(): ConfigService
    {
        return app(ConfigService::class);
    }
}

if (!function_exists('get_user_config')) {
    /**
     * Get configuration for a user with experiments applied.
     *
     * @param mixed $user
     * @param string $type
     * @param array $attributes
     * @return array
     */
    function get_user_config($user, string $type, array $attributes = []): array
    {
        return config_service()->getConfig($user, $type, $attributes);
    }
}

if (!function_exists('active_experiment')) {
    /**
     * Get the active experiment assignment for a user.
     *
     * @param mixed $user
     * @param string $type
     * @return ExperimentAssignment|null
     */
    function active_experiment($user, string $type): ?ExperimentAssignment
    {
        return ExperimentAssignment::where('experimentable_type', get_class($user))
            ->where('experimentable_id', $user->id)
            ->whereHas('experiment', function ($query) use ($type) {
                $query->where('type', $type)->where('is_active', true);
            })
            ->first();
    }
}

if (!function_exists('experiment_stats')) {
    /**
     * Get statistics for an experiment.
     *
     * @param Experiment|int $experiment
     * @return array
     */
    function experiment_stats($experiment): array
    {
        if (is_int($experiment)) {
            $experiment = Experiment::find($experiment);
        }

        if (!$experiment) {
            return [];
        }

        return experiment_service()->getExperimentStats($experiment);
    }
}

if (!function_exists('is_in_experiment')) {
    /**
     * Check if a user is in a specific experiment.
     *
     * @param mixed $user
     * @param string|int $experiment Experiment name or ID
     * @return bool
     */
    function is_in_experiment($user, $experiment): bool
    {
        $query = ExperimentAssignment::where('experimentable_type', get_class($user))
            ->where('experimentable_id', $user->id);

        if (is_int($experiment)) {
            $query->where('experiment_id', $experiment);
        } else {
            $query->whereHas('experiment', function ($q) use ($experiment) {
                $q->where('name', $experiment);
            });
        }

        return $query->exists();
    }
}

if (!function_exists('experiment_variant')) {
    /**
     * Get the variant (flow) a user is assigned to in an experiment.
     *
     * @param mixed $user
     * @param string|int $experiment
     * @return \Jawabapp\RemoteConfig\Models\Flow|null
     */
    function experiment_variant($user, $experiment)
    {
        $query = ExperimentAssignment::where('experimentable_type', get_class($user))
            ->where('experimentable_id', $user->id)
            ->with('flow');

        if (is_int($experiment)) {
            $query->where('experiment_id', $experiment);
        } else {
            $query->whereHas('experiment', function ($q) use ($experiment) {
                $q->where('name', $experiment);
            });
        }

        $assignment = $query->first();

        return $assignment?->flow;
    }
}
