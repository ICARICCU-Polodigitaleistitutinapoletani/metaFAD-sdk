<?php

class metafad_workflow_instanceActivities_controllers_ajax_ToggleComplete extends pinaxcms_contents_controllers_moduleEdit_ajax_Save
{
    public function execute($data)
    {
        $decodeData = json_decode($data);
        if ($decodeData->instanceActivityId) {
            $instanceActivitiesProxy = pinax_ObjectFactory::createObject('metafad.workflow.instanceActivities.models.proxy.InstanceActivitiesProxy');
            $processProxy = pinax_ObjectFactory::createObject('metafad.workflow.processes.models.proxy.ProcessesProxy');
            $document = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');

            if ($document->load($decodeData->instanceActivityId)) {
                if ($document->getRawData()->percentage && $document->percentage == 100) {
                    return;
                }
            }

            $myData = new stdClass();
            $myData->status = 2;
            $myData->percentage = 100;

            $instanceActivitiesProxy->modify($decodeData->instanceActivityId, $myData);
            $processProxy->updatePercentage($decodeData->processId);

            $this->directOutput = true;
            return array('url' => 'dashboard');
        }
    }
}