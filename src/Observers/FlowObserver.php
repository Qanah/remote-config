<?php

namespace Jawabapp\RemoteConfig\Observers;

use Jawabapp\RemoteConfig\Models\Flow;
use Jawabapp\RemoteConfig\Models\FlowLog;

class FlowObserver
{
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
            'log_info' => collect($flow->toArray())->except(['id', 'created_at', 'updated_at'])->toArray(),
        ]);
    }
}
