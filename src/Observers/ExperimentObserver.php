<?php

namespace Jawabapp\RemoteConfig\Observers;

use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\ExperimentLog;

class ExperimentObserver
{
    /**
     * Handle the Experiment "saved" event.
     */
    public function saved(Experiment $experiment): void
    {
        if (!config('remote-config.audit_logging.enabled', true)) {
            return;
        }

        ExperimentLog::create([
            'experiment_id' => $experiment->id,
            'log_user_id' => auth()->id() ?? null,
            'log_info' => collect($experiment->toArray())->except(['id', 'created_at', 'updated_at'])->toArray(),
        ]);
    }
}
