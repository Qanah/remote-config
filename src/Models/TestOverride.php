<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Support\Facades\Cache;

/**
 * Test Override Model - Laravel Cache-based storage for IP-based test flows.
 * Uses Laravel's Cache facade with Redis driver and cache tags for efficient management.
 */
class TestOverride
{
    private const CACHE_TAG = 'remote_config_testing';
    private const CACHE_PREFIX = 'test_override:';
    private const INDEX_KEY = 'index';

    private string $store;

    public function __construct()
    {
        $this->store = config('remote-config.testing.cache_store', 'redis');
    }

    /**
     * Get all test overrides.
     */
    public static function all()
    {
        $model = new self();
        $index = $model->cache()->get(self::INDEX_KEY, []);

        return collect($index)->map(function ($key) use ($model) {
            $data = $model->cache()->get(self::CACHE_PREFIX . $key);

            if (!$data) {
                return null;
            }

            return array_merge(['key' => $key], $data);
        })->filter()->values();
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
        $model = new self();
        $data = $model->cache()->get(self::CACHE_PREFIX . $key);

        if (!$data) {
            return null;
        }

        return array_merge(['key' => $key], $data);
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

        // Store the data in cache
        $model->cache()->forever(self::CACHE_PREFIX . $key, $data);

        // Add to index
        $model->addToIndex($key);

        return $key;
    }

    /**
     * Update a test override.
     */
    public static function update(string $key, array $data): void
    {
        $model = new self();

        // Get existing data to merge
        $existing = $model->cache()->get(self::CACHE_PREFIX . $key, []);
        $updated = array_merge($existing, $data);

        // Update in cache
        $model->cache()->forever(self::CACHE_PREFIX . $key, $updated);
    }

    /**
     * Delete a test override by key.
     */
    public static function delete(string $key): void
    {
        $model = new self();

        // Remove from cache
        $model->cache()->forget(self::CACHE_PREFIX . $key);

        // Remove from index
        $model->removeFromIndex($key);
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

        // Get all keys from index
        $index = $model->cache()->get(self::INDEX_KEY, []);

        // Delete each cached item
        foreach ($index as $key) {
            $model->cache()->forget(self::CACHE_PREFIX . $key);
        }

        // Clear the index
        $model->cache()->forget(self::INDEX_KEY);
    }

    /**
     * Check if a test override exists for IP and type.
     */
    public static function exists(string $ip, string $type): bool
    {
        return self::findByIpAndType($ip, $type) !== null;
    }

    /**
     * Get the cache instance with tags.
     */
    private function cache()
    {
        return Cache::store($this->store)->tags([self::CACHE_TAG]);
    }

    /**
     * Add a key to the index.
     */
    private function addToIndex(string $key): void
    {
        $index = $this->cache()->get(self::INDEX_KEY, []);

        if (!in_array($key, $index)) {
            $index[] = $key;
            $this->cache()->forever(self::INDEX_KEY, $index);
        }
    }

    /**
     * Remove a key from the index.
     */
    private function removeFromIndex(string $key): void
    {
        $index = $this->cache()->get(self::INDEX_KEY, []);
        $index = array_filter($index, fn($k) => $k !== $key);
        $this->cache()->forever(self::INDEX_KEY, array_values($index));
    }
}