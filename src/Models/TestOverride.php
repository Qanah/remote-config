<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Support\Facades\Redis;

/**
 * Test Override Model - Redis-based storage for IP-based test flows.
 * Stores testing overrides as Redis hashes for better performance and simpler structure.
 */
class TestOverride
{
    private string $pattern = 'remote_config_testing:*';
    private string $connection;

    public function __construct()
    {
        $this->connection = config('remote-config.redis.connection', 'default');
    }

    /**
     * Get all test overrides.
     */
    public static function all()
    {
        $model = new self();
        $keys = Redis::connection($model->connection)->keys($model->pattern);

        return collect($keys)->map(function ($key) use ($model) {
            $data = Redis::connection($model->connection)->hgetall($key);
            return array_merge([
                'key' => str_replace($model->getPatternPrefix(), '', $key)
            ], $data);
        });
    }

    /**
     * Get all test overrides for a specific type.
     */
    public static function getAllForType(string $type): array
    {
        $all = self::all();

        $overrides = [];
        foreach ($all as $item) {
            // Check if required keys exist
            if (isset($item['type']) && isset($item['ip']) && isset($item['flow_id'])) {
                if ($item['type'] === $type) {
                    $overrides[$item['ip']] = (int) $item['flow_id'];
                }
            }
        }

        return $overrides;
    }

    /**
     * Find a test override by key.
     */
    public static function find(string $key): ?array
    {
        $all = self::all();
        $found = $all->firstWhere('key', $key);
        return $found ? $found : null;
    }

    /**
     * Find a test override by IP and type.
     */
    public static function findByIpAndType(string $ip, string $type): ?array
    {
        $all = self::all();

        // Filter with isset checks to avoid errors
        $found = $all->filter(function($item) use ($ip, $type) {
            return isset($item['ip']) && isset($item['type'])
                && $item['ip'] === $ip
                && $item['type'] === $type;
        })->first();

        return $found ? $found : null;
    }

    /**
     * Create a new test override.
     */
    public static function create(array $data): string
    {
        $model = new self();
        $key = uniqid('test_');
        $fullKey = $model->getPatternPrefix() . $key;

        Redis::connection($model->connection)->hmset($fullKey, $data);

        return $key;
    }

    /**
     * Update a test override.
     */
    public static function update(string $key, array $data): void
    {
        $model = new self();
        $fullKey = $model->getPatternPrefix() . $key;

        Redis::connection($model->connection)->hmset($fullKey, $data);
    }

    /**
     * Delete a test override by key.
     */
    public static function delete(string $key): void
    {
        $model = new self();
        $fullKey = $model->getPatternPrefix() . $key;

        Redis::connection($model->connection)->del($fullKey);
    }

    /**
     * Delete a test override by IP and type.
     */
    public static function deleteByIpAndType(string $ip, string $type): bool
    {
        $found = self::findByIpAndType($ip, $type);

        if ($found && isset($found['key'])) {
            self::delete($found['key']);
            return true;
        }

        return false;
    }

    /**
     * Clear all test overrides.
     */
    public static function clear(): void
    {
        $model = new self();
        $keys = Redis::connection($model->connection)->keys($model->pattern);

        if (!empty($keys)) {
            Redis::connection($model->connection)->del($keys);
        }
    }

    /**
     * Get the pattern prefix (without wildcard).
     */
    private function getPatternPrefix(): string
    {
        return rtrim($this->pattern, '*');
    }

    /**
     * Check if a test override exists for IP and type.
     */
    public static function exists(string $ip, string $type): bool
    {
        return self::findByIpAndType($ip, $type) !== null;
    }
}