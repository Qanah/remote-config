<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Test Override Model - Redis-based storage for IP-based test flows.
 * This is not a database model but uses Redis for temporary test configurations.
 */
class TestOverride
{
    protected string $ip;
    protected string $flowType;
    protected ?int $flowId;
    protected ?array $content;

    public function __construct(string $ip, string $flowType)
    {
        $this->ip = $ip;
        $this->flowType = $flowType;
    }

    /**
     * Get cache key for this test override.
     */
    protected function getCacheKey(): string
    {
        $prefix = config('remote-config.testing.cache_key_prefix', 'remote_config_test_');
        return $prefix . $this->flowType . '_' . str_replace('.', '_', $this->ip);
    }

    /**
     * Set a test override for this IP and flow type.
     */
    public function set(int $flowId, ?int $ttl = null): bool
    {
        $flow = Flow::find($flowId);

        if (!$flow) {
            return false;
        }

        $this->flowId = $flowId;
        $this->content = $flow->content;

        $ttl = $ttl ?? config('remote-config.cache_ttl', 604800);
        $connection = config('remote-config.testing.redis_connection', 'default');

        $data = [
            'flow_id' => $this->flowId,
            'content' => $this->content,
            'created_at' => now()->toIso8601String(),
        ];

        if (config('cache.default') === 'redis') {
            return Cache::store('redis')->put($this->getCacheKey(), json_encode($data), $ttl);
        }

        return Cache::put($this->getCacheKey(), json_encode($data), $ttl);
    }

    /**
     * Get the test override for this IP and flow type.
     */
    public function get(): ?array
    {
        if (config('cache.default') === 'redis') {
            $cached = Cache::store('redis')->get($this->getCacheKey());
        } else {
            $cached = Cache::get($this->getCacheKey());
        }

        if (!$cached) {
            return null;
        }

        $data = json_decode($cached, true);

        $this->flowId = $data['flow_id'] ?? null;
        $this->content = $data['content'] ?? null;

        return $data;
    }

    /**
     * Delete the test override for this IP and flow type.
     */
    public function delete(): bool
    {
        if (config('cache.default') === 'redis') {
            return Cache::store('redis')->forget($this->getCacheKey());
        }

        return Cache::forget($this->getCacheKey());
    }

    /**
     * Get all test overrides for a flow type.
     */
    public static function getAllForType(string $flowType): array
    {
        $prefix = config('remote-config.testing.cache_key_prefix', 'remote_config_test_');
        $pattern = $prefix . $flowType . '_*';

        $overrides = [];

        if (config('cache.default') === 'redis') {
            $connection = config('remote-config.testing.redis_connection', 'default');
            $redis = Redis::connection($connection);
            $keys = $redis->keys($pattern);

            foreach ($keys as $key) {
                // Remove prefix from key name
                $keyName = str_replace('laravel_database_', '', $key);
                $data = Cache::store('redis')->get($keyName);

                if ($data) {
                    $decoded = json_decode($data, true);
                    $ip = str_replace($prefix . $flowType . '_', '', $keyName);
                    $ip = str_replace('_', '.', $ip);

                    $overrides[] = [
                        'ip' => $ip,
                        'flow_id' => $decoded['flow_id'] ?? null,
                        'content' => $decoded['content'] ?? null,
                        'created_at' => $decoded['created_at'] ?? null,
                    ];
                }
            }
        }

        return $overrides;
    }

    /**
     * Check if a test override exists for this IP and flow type.
     */
    public function exists(): bool
    {
        return $this->get() !== null;
    }

    /**
     * Get the flow ID.
     */
    public function getFlowId(): ?int
    {
        if ($this->flowId === null) {
            $this->get();
        }

        return $this->flowId;
    }

    /**
     * Get the content.
     */
    public function getContent(): ?array
    {
        if ($this->content === null) {
            $this->get();
        }

        return $this->content;
    }
}
