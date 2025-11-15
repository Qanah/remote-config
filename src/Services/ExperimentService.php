<?php

declare(strict_types=1);

namespace Jawabapp\RemoteConfig\Services;

use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\Flow;

class ExperimentService
{
    protected RedisCounterService $redisCounter;

    /**
     * ExperimentService constructor.
     *
     * @param RedisCounterService|null $redisCounter
     */
    public function __construct(?RedisCounterService $redisCounter = null)
    {
        $this->redisCounter = $redisCounter ?? app(RedisCounterService::class);
    }

    /**
     * Select a flow variant from an experiment based on ratios.
     *
     * @param Experiment $experiment
     * @return Flow|null
     */
    public function selectFlow(Experiment $experiment): ?Flow
    {
        $flows = $experiment->flows;

        if ($flows->isEmpty()) {
            return null;
        }

        // Get current counters for all flows
        $flowCounters = [];
        $totalAssigned = 0;

        foreach ($flows as $flow) {
            $key = "experiment:{$experiment->id}:flow:{$flow->id}";
            $count = $this->redisCounter->getCounter($key);
            $flowCounters[$flow->id] = $count;
            $totalAssigned += $count;
        }

        // If no assignments yet, return first flow
        if ($totalAssigned === 0) {
            $firstFlow = $flows->first();
            $key = "experiment:{$experiment->id}:flow:{$firstFlow->id}";
            $this->redisCounter->incrementAndGet($key);
            return $firstFlow;
        }

        // Find the flow that needs more assignments to reach its ratio
        foreach ($flows as $flow) {
            $targetRatio = $flow->pivot->ratio / 100;
            $currentRatio = $flowCounters[$flow->id] / $totalAssigned;

            // If this flow is under its target ratio, assign to it
            if ($currentRatio < $targetRatio) {
                $key = "experiment:{$experiment->id}:flow:{$flow->id}";
                $this->redisCounter->incrementAndGet($key);
                return $flow;
            }
        }

        // All flows are at or above their target ratios, assign to the one furthest below
        $mostUnderserved = null;
        $largestDifference = 0;

        foreach ($flows as $flow) {
            $targetRatio = $flow->pivot->ratio / 100;
            $currentRatio = $flowCounters[$flow->id] / $totalAssigned;
            $difference = $targetRatio - $currentRatio;

            if ($difference > $largestDifference) {
                $largestDifference = $difference;
                $mostUnderserved = $flow;
            }
        }

        if ($mostUnderserved) {
            $key = "experiment:{$experiment->id}:flow:{$mostUnderserved->id}";
            $this->redisCounter->incrementAndGet($key);
            return $mostUnderserved;
        }

        // Fallback: return first flow
        $firstFlow = $flows->first();
        $key = "experiment:{$experiment->id}:flow:{$firstFlow->id}";
        $this->redisCounter->incrementAndGet($key);
        return $firstFlow;
    }

    /**
     * Get the counter value for a specific flow in an experiment.
     *
     * @param int $experimentId
     * @param int $flowId
     * @return int
     */
    public function getFlowCount(int $experimentId, int $flowId): int
    {
        $key = "experiment:{$experimentId}:flow:{$flowId}";
        return $this->redisCounter->getCounter($key);
    }

    /**
     * Clear all counters for an experiment.
     *
     * @param Experiment $experiment
     * @return void
     */
    public function clearExperimentCounters(Experiment $experiment): void
    {
        $this->redisCounter->resetExperimentCounters($experiment->id);
    }

    /**
     * Get statistics for an experiment from Redis counters.
     *
     * @param Experiment $experiment
     * @return array
     */
    public function getExperimentStats(Experiment $experiment): array
    {
        $flows = $experiment->flows;
        $stats = [];
        $total = 0;

        $counters = $this->redisCounter->getExperimentCounters($experiment->id);

        foreach ($flows as $flow) {
            $count = $counters[$flow->id] ?? 0;
            $total += $count;

            $stats[] = [
                'flow_id' => $flow->id,
                'flow_type' => $flow->type,
                'ratio' => $flow->pivot->ratio,
                'count' => $count,
            ];
        }

        // Calculate percentages
        foreach ($stats as &$stat) {
            $stat['percentage'] = $total > 0 ? round(($stat['count'] / $total) * 100, 2) : 0;
        }

        return [
            'total' => $total,
            'flows' => $stats,
        ];
    }
}