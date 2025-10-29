<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jawabapp\RemoteConfig\Traits\HasDynamicRelation;

class Experiment extends Model
{
    use HasDynamicRelation;

    protected $fillable = [
        'name',
        'type',
        'overwrite_id',
        'platforms',
        'countries',
        'languages',
        'user_created_after_date',
        'is_active',
    ];

    protected $casts = [
        'platforms' => 'array',
        'countries' => 'array',
        'languages' => 'array',
        'user_created_after_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('remote-config.table_prefix', '');
        $this->table = $prefix . 'experiments';
    }

    /**
     * The flow variants attached to this experiment.
     */
    public function flows(): BelongsToMany
    {
        $prefix = config('remote-config.table_prefix', '');

        return $this->belongsToMany(Flow::class, $prefix . 'experiment_flow')
            ->withPivot(['ratio'])
            ->withTimestamps();
    }

    /**
     * User assignments for this experiment.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ExperimentAssignment::class);
    }

    /**
     * Audit logs for this experiment.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ExperimentLog::class);
    }

    /**
     * Confirmations for this experiment.
     */
    public function confirmations(): HasMany
    {
        return $this->hasMany(Confirmation::class);
    }

    /**
     * Get active experiments for specific criteria.
     */
    public static function getActiveExperiment(
        string $type,
        ?int $overwriteId = null,
        ?string $platform = null,
        ?string $country = null,
        ?string $language = null
    ): ?self {
        $query = self::where('is_active', true)->where('type', $type);

        if ($overwriteId !== null) {
            $query->where('overwrite_id', $overwriteId);
        }

        $isSqlite = config('database.default') === 'sqlite';

        if ($platform) {
            if ($isSqlite) {
                $query->where('platforms', 'LIKE', '%"' . $platform . '"%');
            } else {
                $query->whereRaw("JSON_SEARCH(platforms, 'one', ?) is not null", [$platform]);
            }
        }

        if ($country) {
            if ($isSqlite) {
                $query->where('countries', 'LIKE', '%"' . $country . '"%');
            } else {
                $query->whereRaw("JSON_SEARCH(countries, 'one', ?) is not null", [$country]);
            }
        }

        if ($language) {
            if ($isSqlite) {
                $query->where('languages', 'LIKE', '%"' . $language . '"%');
            } else {
                $query->whereRaw("JSON_SEARCH(languages, 'one', ?) is not null", [$language]);
            }
        }

        return $query->first();
    }

    /**
     * Check if this experiment conflicts with another active experiment.
     */
    public function hasConflict(): bool
    {
        $query = self::where('is_active', true)
            ->where('type', $this->type)
            ->where('overwrite_id', $this->overwrite_id)
            ->where('id', '!=', $this->id ?? 0);

        $isSqlite = config('database.default') === 'sqlite';

        // Check for overlapping platforms, countries, and languages
        if (!empty($this->platforms)) {
            foreach ($this->platforms as $platform) {
                if ($isSqlite) {
                    $query->orWhere('platforms', 'LIKE', '%"' . $platform . '"%');
                } else {
                    $query->orWhereRaw("JSON_SEARCH(platforms, 'one', ?) is not null", [$platform]);
                }
            }
        }

        return $query->exists();
    }

    /**
     * Get the flow variants with their ratios.
     */
    public function getFlowsWithRatios(): array
    {
        return $this->flows->map(function ($flow) {
            return [
                'id' => $flow->id,
                'type' => $flow->type,
                'ratio' => $flow->pivot->ratio,
            ];
        })->toArray();
    }
}
