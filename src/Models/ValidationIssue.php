<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ValidationIssue extends Model
{
    protected $fillable = [
        'experimentable_type',
        'experimentable_id',
        'platform',
        'path',
        'invalid_value',
        'type',
        'error_message',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('remote-config.table_prefix', '');
        $this->table = $prefix . 'validation_issues';
    }

    /**
     * The user/entity who reported the issue (polymorphic).
     */
    public function experimentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log a validation issue.
     */
    public static function logIssue(
        $experimentable,
        string $path,
        $invalidValue,
        ?string $platform = null,
        ?string $type = null,
        ?string $errorMessage = null
    ): self {
        return self::create([
            'experimentable_type' => get_class($experimentable),
            'experimentable_id' => $experimentable->id,
            'platform' => $platform,
            'path' => $path,
            'invalid_value' => is_array($invalidValue) ? json_encode($invalidValue) : $invalidValue,
            'type' => $type,
            'error_message' => $errorMessage,
        ]);
    }
}
