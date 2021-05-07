<?php
class metafad_jobmanager_JobFactory extends PinaxObject
{
    public function createJob($name, $params, $description, $type = 'INTERACTIVE')
    {
        $ar = pinax_ObjectFactory::createModel('metafad.jobmanager.models.Job');
        $ar->job_type = $type;
        $ar->job_name = $name;
        $ar->job_params = serialize($params);
        $ar->job_description = $description;
        $ar->job_status = metafad_jobmanager_JobStatus::NOT_STARTED;
        $ar->job_progress = 0;
        $ar->job_creationDate = new pinax_types_DateTime();
        $ar->job_modificationDate = new pinax_types_DateTime();
        $ar->save();
    }
}