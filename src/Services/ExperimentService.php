<?php

declare(strict_types=1);

namespace Jawabapp\RemoteConfig\Services;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\Flow;

class ExperimentService
{
    /**
     * Cache store for experiment counters.
     *
     * @var Repository
     */
    private Repository $store;

    /**
     * The key of the experiment for cookie.
     *
     * @var string
     */
    private string $key;

    /**
     * ExperimentService constructor.
     *
     * @param string $key
     * @param Repository|null $store
     */
    public function __construct(string $key = 'experiment', ?Repository $store = null)
    {
        $this->store = $store ?? Cache::store();
        $this->key = $key;
    }

    /**
     * Static factory method.
     *
     * @param string $key
     * @param Repository|null $store
     * @return self
     */
    public static function make(string $key = 'experiment', ?Repository $store = null): self
    {
        return new static($key, $store);
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

        // Build array of flow IDs with their ratios
        $variants = [];
        foreach ($flows as $flow) {
            $key = "exp_{$experiment->id}_flow_{$flow->id}";
            $variants[$key] = $flow->pivot->ratio;
        }

        // Select a variant based on ratios
        $selectedKey = $this->start($variants);

        if (!$selectedKey) {
            return null;
        }

        // Extract flow ID from key
        preg_match('/flow_(\d+)$/', $selectedKey, $matches);
        $flowId = $matches[1] ?? null;

        return $flows->firstWhere('id', $flowId);
    }

    /**
     * Start variant selection and save to cookie.
     *
     * @param array $variants
     * @return string|null
     */
    public function startAndSaveCookie(array $variants): ?string
    {
        $selected = $this->start($variants);

        if ($selected) {
            $cookieConfig = config('remote-config.cookie', []);
            Cookie::queue(cookie(
                $this->key,
                $selected,
                $cookieConfig['ttl'] ?? 525600, // 1 year
                $cookieConfig['path'] ?? '/',
                $cookieConfig['domain'] ?? null,
                $cookieConfig['secure'] ?? false,
                $cookieConfig['http_only'] ?? true,
                false,
                $cookieConfig['same_site'] ?? 'lax'
            ));
        }

        return $selected;
    }

    /**
     * Begin variant selection based on ratio distribution.
     *
     * @param array $variants Array of [key => ratio]
     * @return string|null
     */
    public function start(array $variants): ?string
    {
        if (empty($variants)) {
            return null;
        }

        // Check if already selected via request/cookie
        $existing = $this->getKeyFromRequest();
        if ($existing !== null && isset($variants[$existing])) {
            return $existing;
        }

        // Get current counts for all variants
        $counts = $this->prepareVariants($variants);

        // Find first variant that hasn't reached its quota
        foreach ($variants as $key => $maxValue) {
            if ($counts[$key] >= $maxValue) {
                continue;
            }

            // Increment counter for this variant
            $this->store->increment($key);

            return $key;
        }

        // All variants reached their quota, reset and try again
        $this->resetCounters($counts);

        return $this->start($variants);
    }

    /**
     * Get variant key from request or cookie.
     *
     * @return string|null
     */
    private function getKeyFromRequest(): ?string
    {
        return request()->get($this->key)
            ?? request()->cookie($this->key)
            ?? $_GET[$this->key] ?? null
            ?? $_COOKIE[$this->key] ?? null;
    }

    /**
     * Get current counts for all variants.
     *
     * @param array $variants
     * @return array
     */
    private function prepareVariants(array $variants = []): array
    {
        $counts = [];

        foreach ($variants as $key => $ratio) {
            $counts[$key] = (int) $this->store->get($key, 0);
        }

        return $counts;
    }

    /**
     * Reset counters for all variants.
     *
     * @param array $counts
     * @return void
     */
    private function resetCounters(array $counts): void
    {
        foreach ($counts as $key => $value) {
            if ($value > 0) {
                $this->store->decrement($key, $value);
            }
        }
    }

    /**
     * Get the counter value for a specific variant.
     *
     * @param string $key
     * @return int
     */
    public function getCount(string $key): int
    {
        return (int) $this->store->get($key, 0);
    }

    /**
     * Clear all counters for an experiment.
     *
     * @param Experiment $experiment
     * @return void
     */
    public function clearExperimentCounters(Experiment $experiment): void
    {
        $flows = $experiment->flows;

        foreach ($flows as $flow) {
            $key = "exp_{$experiment->id}_flow_{$flow->id}";
            $this->store->forget($key);
        }
    }

    /**
     * Get statistics for an experiment.
     *
     * @param Experiment $experiment
     * @return array
     */
    public function getExperimentStats(Experiment $experiment): array
    {
        $flows = $experiment->flows;
        $stats = [];
        $total = 0;

        foreach ($flows as $flow) {
            $key = "exp_{$experiment->id}_flow_{$flow->id}";
            $count = $this->getCount($key);
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
            $stat['percentage'] = $total > 0 ? ($stat['count'] / $total) * 100 : 0;
        }

        return [
            'total' => $total,
            'flows' => $stats,
        ];
    }
}
