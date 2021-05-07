<?php
class metafad_jobmanager_controllers_JobManager extends metafad_common_controllers_Command
{
    public function execute()
    {
        // esegue il primo job non ancora eseguito
        $it = pinax_ObjectFactory::createModelIterator('metafad.jobmanager.models.Job');
        $it->where('job_status', 'NOT_STARTED');

        if ($it->count() > 0) {
            $ar = $it->first();
            $jobService = pinax_ObjectFactory::createObject($ar->job_name, $ar->job_id);
            $jobService->run();
        }
    }

}