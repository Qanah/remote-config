<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Confirmation extends Model
{
    protected $fillable = [
        'experimentable_type',
        'experimentable_id',
        'experiment_id',
        'flow_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('remote-config.table_prefix', '');
        $this->table = $prefix . 'confirmations';
    }

    /**
     * The user/entity who confirmed (polymorphic).
     */
    public function experimentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The experiment that was confirmed.
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * The flow variant that was confirmed.
     */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(Flow::class);
    }

    /**
     * Check if user has confirmed an experiment.
     */
    public static function hasConfirmed($experimentable, int $experimentId): bool
    {
        return self::where('experimentable_type', get_class($experimentable))
            ->where('experimentable_id', $experimentable->id)
            ->where('experiment_id', $experimentId)
            ->where('status', 'confirmed')
            ->exists();
    }

    /**
     * Mark as confirmed.
     */
    public function confirm(): bool
    {
        $this->status = 'confirmed';
        return $this->save();
    }
}
