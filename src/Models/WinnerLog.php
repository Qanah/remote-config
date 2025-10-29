<?php

namespace Jawabapp\RemoteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WinnerLog extends Model
{
    protected $fillable = [
        'winner_id',
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
        $this->table = $prefix . 'winner_logs';
    }

    /**
     * The winner that was changed.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Winner::class);
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
