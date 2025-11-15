<?php

namespace Jawabapp\RemoteConfig\Services;

use Illuminate\Support\Facades\Redis;

class RedisCounterService
{
    protected string $keyPrefix;
    protected string $connection;

    public function __construct()
    {
        $this->keyPrefix = config('remote-config.redis.key_prefix', 'remote_config:counter:');
        $this->connection = config('remote-config.redis.connection', 'default');
    }

    /**
     * Atomically increment a counter and return the new value.
     *
     * @param string $key
     * @return int
     */
    public function incrementAndGet(string $key): int
    {
        $fullKey = $this->keyPrefix . $key;
        return Redis::connection($this->connection)->incr($fullKey);
    }

    /**
     * Get current counter value.
     *
     * @param string $key
     * @return int
     */
    public function getCounter(string $key): int
    {
        $fullKey = $this->keyPrefix . $key;
        $value = Redis::connection($this->connection)->get($fullKey);
        return $value ? (int) $value : 0;
    }

    /**
     * Reset counter to zero.
     *
     * @param string $key
     * @return void
     */
    public function resetCounter(string $key): void
    {
        $fullKey = $this->keyPrefix . $key;
        Redis::connection($this->connection)->del($fullKey);
    }

    /**
     * Get counters for all flows in an experiment.
     *
     * @param int $experimentId
     * @return array
     */
    public function getExperimentCounters(int $experimentId): array
    {
        $pattern = $this->keyPrefix . "experiment:{$experimentId}:flow:*";
        $keys = Redis::connection($this->connection)->keys($pattern);

        $counters = [];
        foreach ($keys as $key) {
            $value = Redis::connection($this->connection)->get($key);
            // Extract flow_id from key
            preg_match('/flow:(\d+)$/', $key, $matches);
            if (isset($matches[1])) {
                $counters[(int) $matches[1]] = (int) $value;
            }
        }

        return $counters;
    }

    /**
     * Reset all counters for an experiment.
     *
     * @param int $experimentId
     * @return void
     */
    public function resetExperimentCounters(int $experimentId): void
    {
        $pattern = $this->keyPrefix . "experiment:{$experimentId}:flow:*";
        $keys = Redis::connection($this->connection)->keys($pattern);

        if (!empty($keys)) {
            Redis::connection($this->connection)->del($keys);
        }
    }
}