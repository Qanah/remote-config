<?php

namespace Jawabapp\RemoteConfig\Observers;

use Jawabapp\RemoteConfig\Models\Flow;
use Jawabapp\RemoteConfig\Models\FlowLog;

class FlowObserver
{
    /**
     * Handle the Flow "saving" event.
     * Automatically maintain the default_type column for MySQL unique constraint.
     */
    public function saving(Flow $flow): void
    {
        // Set default_type to enforce unique constraint (MySQL-compatible)
        // default_type = type when is_default = true, NULL otherwise
        $flow->default_type = $flow->is_default ? $flow->type : null;
    }

    /**
     * Handle the Flow "saved" event.
     */
    public function saved(Flow $flow): void
    {
        if (!config('remote-config.audit_logging.enabled', true)) {
            return;
        }

        FlowLog::create([
            'flow_id' => $flow->id,
            'log_user_id' => auth()->id() ?? null,
            'log_info' => collect($flow->toArray())->except(['id', 'created_at', 'updated_at', 'default_type', 'display_label'])->toArray(),
        ]);
    }
}
