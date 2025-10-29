<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExperimentAssignmentLog extends Model
{
    protected $fillable = [
        'experiment_assignment_id',
        'experimentable_type',
        'experimentable_id',
        'experiment_id',
        'flow_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('remote-config.table_prefix', '');
        $this->table = $prefix . 'experiment_assignment_logs';
    }

    /**
     * The assignment that was logged.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ExperimentAssignment::class, 'experiment_assignment_id');
    }

    /**
     * The user/entity (polymorphic).
     */
    public function experimentable(): MorphTo
    {
        return $this->morphTo();
    }
}
