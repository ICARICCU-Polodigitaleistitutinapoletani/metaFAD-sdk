<?php

class metafad_workflow_processes_controllers_ToggleStart extends metafad_common_controllers_Command
{
    public function execute($id)
    {
        $this->checkPermissionForBackend('edit');

        if ($id) {
            $processesProxy = pinax_ObjectFactory::createObject('metafad.workflow.processes.models.proxy.ProcessesProxy');
            $instanceActivitiesProxy = pinax_ObjectFactory::createObject('metafad.workflow.instanceActivities.models.proxy.InstanceActivitiesProxy');

            $document = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');

            $data = new stdClass();
            $data->status = 1;
            $data->processStatus = 1;

            if($document->load($id)) {
                if ($document->getRawData()->instanceActivities) {
                    foreach ($document->instanceActivities as $instanceId) {
                        $instanceActivitiesProxy->modify($instanceId, $data);
                    }
                }
            }

            $data->percentage = 0;

            $processesProxy->modify($id, $data);

            pinax_helpers_Navigation::goHere();
        }
    }
}