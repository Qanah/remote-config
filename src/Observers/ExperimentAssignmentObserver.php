<?php

namespace Jawabapp\RemoteConfig\Observers;

use Jawabapp\RemoteConfig\Models\ExperimentAssignment;
use Jawabapp\RemoteConfig\Models\ExperimentAssignmentLog;

class ExperimentAssignmentObserver
{
    /**
     * Handle the ExperimentAssignment "created" event.
     */
    public function created(ExperimentAssignment $assignment): void
    {
        if (!config('remote-config.audit_logging.log_assignments', true)) {
            return;
        }

        ExperimentAssignmentLog::create([
            'experiment_assignment_id' => $assignment->id,
            'experimentable_type' => $assignment->experimentable_type,
            'experimentable_id' => $assignment->experimentable_id,
            'experiment_id' => $assignment->experiment_id,
            'flow_id' => $assignment->flow_id,
        ]);
    }
}
