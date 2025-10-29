<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperimentLog extends Model
{
    protected $fillable = [
        'experiment_id',
        'log_user_id',
        'log_info',
    ];

    protected $casts = [
        'log_info' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('remote-config.table_prefix', '');
        $this->table = $prefix . 'experiment_logs';
    }

    /**
     * The experiment that was changed.
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * The user who made the change.
     */
    public function user(): BelongsTo
    {
        $userModel = config('remote-config.experimentable_model', \App\Models\User::class);

        return $this->belongsTo($userModel, 'log_user_id');
    }
}
