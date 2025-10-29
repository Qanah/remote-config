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
     * Returns the most specific matching experiment (fewer targets = more specific).
     */
    public static function getActiveExperiment(
        string $type,
        ?string $platform = null,
        ?string $country = null,
        ?string $language = null
    ): ?self {
        $query = self::where('is_active', true)->where('type', $type);

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

        // Get all matching experiments and sort by specificity
        $experiments = $query->get();

        if ($experiments->isEmpty()) {
            return null;
        }

        // Return the most specific experiment (fewest targets = most specific)
        // Specificity score = total number of targeted platforms + countries + languages
        return $experiments->sortBy(function ($experiment) {
            return count($experiment->platforms ?? []) +
                   count($experiment->countries ?? []) +
                   count($experiment->languages ?? []);
        })->first();
    }

    /**
     * Check if this experiment conflicts with another active experiment.
     * Prevents any overlap in platforms, countries, and languages for the same type.
     */
    public function hasConflict(): bool
    {
        $platforms = $this->platforms ?? [];
        $countries = $this->countries ?? [];
        $languages = $this->languages ?? [];

        if (empty($platforms) || empty($countries) || empty($languages)) {
            return false;
        }

        $query = self::where('is_active', true)
            ->where('type', $this->type);

        // Exclude current experiment when updating
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        $isSqlite = config('database.default') === 'sqlite';

        // Build platform conditions (OR logic - match ANY platform)
        $platformConditions = [];
        foreach ($platforms as $platform) {
            if ($isSqlite) {
                $platformConditions[] = "platforms LIKE '%" . addslashes($platform) . "%'";
            } else {
                $platformConditions[] = "JSON_SEARCH(platforms, 'one', '" . addslashes($platform) . "') is not null";
            }
        }

        // Build country conditions (OR logic - match ANY country)
        $countryConditions = [];
        foreach ($countries as $country) {
            if ($isSqlite) {
                $countryConditions[] = "countries LIKE '%" . addslashes($country) . "%'";
            } else {
                $countryConditions[] = "JSON_SEARCH(countries, 'one', '" . addslashes($country) . "') is not null";
            }
        }

        // Build language conditions (OR logic - match ANY language)
        $languageConditions = [];
        foreach ($languages as $language) {
            if ($isSqlite) {
                $languageConditions[] = "languages LIKE '%" . addslashes($language) . "%'";
            } else {
                $languageConditions[] = "JSON_SEARCH(languages, 'one', '" . addslashes($language) . "') is not null";
            }
        }

        // Apply all conditions: (platform1 OR platform2) AND (country1 OR country2) AND (language1 OR language2)
        if (!empty($platformConditions) && !empty($countryConditions) && !empty($languageConditions)) {
            $query->whereRaw('(' . implode(' OR ', $platformConditions) . ')')
                  ->whereRaw('(' . implode(' OR ', $countryConditions) . ')')
                  ->whereRaw('(' . implode(' OR ', $languageConditions) . ')');

            return $query->exists();
        }

        return false;
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
