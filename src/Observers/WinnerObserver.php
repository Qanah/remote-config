<?php

namespace Jawabapp\RemoteConfig\Observers;

use Jawabapp\RemoteConfig\Models\Winner;
use Jawabapp\RemoteConfig\Models\WinnerLog;

class WinnerObserver
{
    /**
     * Handle the Winner "saved" event.
     */
    public function saved(Winner $winner): void
    {
        if (!config('remote-config.audit_logging.enabled', true)) {
            return;
        }

        WinnerLog::create([
            'winner_id' => $winner->id,
            'log_user_id' => auth()->id() ?? null,
            'log_info' => collect($winner->toArray())->except(['id', 'created_at', 'updated_at'])->toArray(),
        ]);
    }
}
