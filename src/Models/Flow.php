<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flow extends Model
{
    public const DEFAULT_TYPE = 'default';
    public const TESTING_TYPE = 'testing';
    public const BACKEND_TYPE = 'backend-config';

    protected $fillable = [
        'type',
        'variant_name',
        'content',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('remote-config.table_prefix', '');
        $this->table = $prefix . 'flows';
    }

    /**
     * The experiments that use this flow variant.
     */
    public function experiments(): BelongsToMany
    {
        $prefix = config('remote-config.table_prefix', '');

        return $this->belongsToMany(Experiment::class, $prefix . 'experiment_flow')
            ->withPivot(['ratio'])
            ->withTimestamps();
    }

    /**
     * The assignments using this flow.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ExperimentAssignment::class);
    }

    /**
     * Audit logs for this flow.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(FlowLog::class);
    }

    /**
     * Winners using this flow.
     */
    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    /**
     * Get a flow configuration by type.
     */
    public static function getConfig(string $type = self::DEFAULT_TYPE): ?self
    {
        static $configs;

        if (empty($configs[$type])) {
            $configs[$type] = self::where('type', $type)
                ->where('is_active', true)
                ->first();
        }

        return $configs[$type];
    }

    /**
     * Get a value from the JSON content using dot notation.
     */
    public function getJsonValue(string $key, $default = null)
    {
        $content = $this->getAttribute('content');

        if (!is_array($content)) {
            $content = json_decode($content, true);
        }

        foreach (explode('.', $key) as $subKey) {
            $content = $content[$subKey] ?? null;
        }

        return $content ?? $default;
    }

    /**
     * Set a value in the JSON content using dot notation.
     */
    public function setJsonValue(string $key, $value): self
    {
        $content = $this->getAttribute('content');

        if (!is_array($content)) {
            $content = json_decode($content, true);
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $content[$key][$k] = $v;
            }
        } else {
            $content[$key] = $value;
        }

        $this->setAttribute('content', $content);

        return $this;
    }
}
