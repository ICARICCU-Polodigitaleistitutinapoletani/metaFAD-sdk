<?php

class metafad_workflow_processes_controllers_Delete extends pinaxcms_contents_controllers_moduleEdit_Delete
{
    public function execute($id, $model)
    {
        $document = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
        $document2 = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');

        if ($document->load($id)) {
            if ($document->getRawData()->instanceActivities) {
                foreach ($document->instanceActivities as $instanceActivity) {
                    $document2->delete($instanceActivity);
                }
            }
        } else {
            return array('http-status' => 400);
        }
        parent::execute($id, $model);
    }
}