<?php

class metafad_workflow_processes_controllers_ajax_Save extends pinaxcms_contents_controllers_moduleEdit_ajax_Save
{
    public function execute($data)
    {
        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');

        $instanceActivitiesProxy = pinax_ObjectFactory::createObject('metafad.workflow.instanceActivities.models.proxy.InstanceActivitiesProxy');

        $document = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');

        $decodeData = json_decode($data);

        $arrayInstances = array();
        if($document->load($decodeData->__id)){
            if($document->getRawData()->instanceActivities){
                $arrayInstances = $document->instanceActivities;
            }
        }

        foreach ($decodeData->activities as $activity) {
            // TODO Gestione utente

            if($activity->note){
                $newNote = new stdClass();
                $newNote->detail = $activity->note;
                $newNote->created_at = date("d/m/Y");
                $newNote->created_by = "Mario Rossi";
                $activity->notes = [$newNote];
                unset($activity->note);
            }
            if(empty($decodeData->__id) && empty($activity->document_id)){
                $instanceActivitiesId[] = $instanceActivitiesProxy->modify(null, $activity, true);
            } else if ($activity->document_id) {
                $instanceActivitiesId[] = $instanceActivitiesProxy->modify($activity->document_id, $activity);
            } else {
                $instanceActivitiesId[] = $instanceActivitiesProxy->modify(null, $activity, true);
            }
        }

        $array0 = array(0);
        if ($instanceActivitiesId) {
            $res = array_values(array_diff($instanceActivitiesId, $array0));
            $decodeData->instanceActivities = $res;
        }

        $result = $contentproxy->saveContent($decodeData);

        $updateData = new stdClass();
        $updateData->processId = $result['__id'];
        $updateData->processTitle = $decodeData->title;
        if($res){
            foreach($res as $id){
                $instanceActivitiesProxy->modify($id, $updateData);
            }

            $arrayDiff = array_diff($arrayInstances, $res);
            foreach($arrayDiff as $id){
                $contentproxy->delete($id, 'metafad.workflow.instanceActivities.models.Model');
            }

        }
        $this->directOutput = true;

        if ($result['__id']) {
            return array('set' => $result);
        } else {
            return array('errors' => $result);
        }
    }
}