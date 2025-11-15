<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExperimentAssignment extends Model
{
    protected $fillable = [
        'experimentable_type',
        'experimentable_id',
        'experiment_id',
        'flow_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('remote-config.table_prefix', '');
        $this->table = $prefix . 'experiment_assignments';
    }

    /**
     * The user/entity assigned to the experiment (polymorphic).
     */
    public function experimentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The experiment this assignment belongs to.
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * The flow variant assigned.
     */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(Flow::class);
    }

    /**
     * Audit logs for this assignment.
     */
    public function logs()
    {
        return $this->hasMany(ExperimentAssignmentLog::class);
    }

    /**
     * Log this assignment and delete it.
     */
    public function logAndDelete(): void
    {
        ExperimentAssignmentLog::create([
            'experiment_assignment_id' => $this->id,
            'experimentable_type' => $this->experimentable_type,
            'experimentable_id' => $this->experimentable_id,
            'experiment_id' => $this->experiment_id,
            'flow_id' => $this->flow_id,
        ]);

        $this->delete();
    }

    /**
     * Get assignment for a specific user and experiment.
     */
    public static function getAssignment($experimentable, int $experimentId): ?self
    {
        return self::where('experimentable_type', get_class($experimentable))
            ->where('experimentable_id', $experimentable->id)
            ->where('experiment_id', $experimentId)
            ->first();
    }
}
