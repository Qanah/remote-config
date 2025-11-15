<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Winner extends Model
{
    protected $fillable = [
        'type',
        'content',
        'platform',
        'country_code',
        'language',
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
        $this->table = $prefix . 'winners';
    }

    /**
     * Audit logs for this winner.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WinnerLog::class);
    }

    /**
     * Get winner for specific targeting criteria.
     */
    public static function getWinner(
        string $type,
        string $platform,
        string $countryCode,
        string $language
    ): ?self {
        return self::where('type', $type)
            ->where('platform', $platform)
            ->where('country_code', $countryCode)
            ->where('language', $language)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if a winner already exists for these criteria.
     */
    public static function exists(
        string $type,
        string $platform,
        string $countryCode,
        string $language,
        ?int $excludeId = null
    ): bool {
        $query = self::where('type', $type)
            ->where('platform', $platform)
            ->where('country_code', $countryCode)
            ->where('language', $language);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
