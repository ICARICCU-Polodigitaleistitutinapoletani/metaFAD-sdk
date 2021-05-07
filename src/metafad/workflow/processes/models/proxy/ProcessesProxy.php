<?php

class metafad_workflow_processes_models_proxy_ProcessesProxy extends PinaxObject
{
    protected $application;

    function __construct()
    {
        $this->application = pinax_ObjectValues::get('org.pinax', 'application');
    }

    public function modify($id, $data, $forceNew = false)
    {
        if ($this->validate($data)) {

            $document = $this->createModel($id, 'metafad.workflow.processes.models.Model');

            foreach ($data as $key => $value) {
                $document->$key = $value;
            }

            try {
                return $document->publish(null, null, $forceNew);
            } catch (pinax_validators_ValidationException $e) {
                return $e->getErrors();
            }
        } else {
            // TODO
        }
    }

    public function validate($data)
    {
        return true;
    }

    protected function createModel($id = null, $model)
    {
        $document = pinax_ObjectFactory::createModel($model);
        if ($id) {
            $document->load($id);
        }
        return $document;
    }

    public function instanceActivityControll($activityId)
    {
        $it = pinax_ObjectFactory::createModelIterator('metafad.workflow.processes.models.Model');
        $instanceActivitiesProxy = pinax_ObjectFactory::createObject('metafad.workflow.instanceActivities.models.proxy.InstanceActivitiesProxy');

        $found = false;
        foreach ($it as $ar) {
            $instancesId = $ar->getRawData()->instanceActivities;
            foreach ($instancesId as $id) {
                if ($instanceActivitiesProxy->activityControll($id, $activityId)) {
                    $found = true;
                }
            }
        }

        return $found;
    }

    public function updatePercentage($id, $activityPercentage = null)
    {
        $document = $this->createModel($id, 'metafad.workflow.processes.models.Model');
        $record = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
        $instanceActivitiesProxy = pinax_ObjectFactory::createObject('metafad.workflow.instanceActivities.models.proxy.InstanceActivitiesProxy');
        $count = 0;

        $countActivities = count($document->instanceActivities);
        foreach ($document->instanceActivities as $instanceId) {
            if ($record->load($instanceId)) {
                if ($record->getRawData()->status && $record->status == 2) {
                    $count++;
                }
            }
        }

        if ($countActivities == $count) {
            $document->percentage = 100;
        } else {
            $updateRate = 100 / $countActivities;

            if ($activityPercentage) {
                $updateRate = ($updateRate * $activityPercentage) / 100;
            }

            $document->percentage = $document->percentage + $updateRate;
            if ($document->percentage < 0) {
                $document->percentage = 0;
            }
        }

        if ($document->percentage == 100) {
            $data = new stdClass();
            $data->processStatus = 2;
            foreach ($document->instanceActivities as $instanceId) {
                $instanceActivitiesProxy->modify($instanceId, $data);
            }
            $document->status = 2;
        } else {
            $document->status = 1;
        }

        return $document->publish(null, null);
    }
}