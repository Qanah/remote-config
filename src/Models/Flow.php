<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flow extends Model
{
    protected $fillable = [
        'type',
        'name',
        'content',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'display_label',
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
     * Get the display label for the flow.
     * Format: "FLOW {type} #{id} {name}"
     */
    protected function displayLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => "FLOW {$this->type} #{$this->id} {$this->name}"
        );
    }

    /**
     * Get a flow configuration by type.
     */
    public static function getConfig(string $type): ?self
    {
        static $configs;

        if (empty($configs[$type])) {
            $configs[$type] = self::where('type', $type)
                ->where('is_default', true)
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
