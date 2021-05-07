<?php

class metafad_workflow_activities_controllers_Delete extends pinaxcms_contents_controllers_moduleEdit_Delete
{
    public function execute($id, $model)
    {
        $processesProxy = pinax_ObjectFactory::createObject('metafad.workflow.processes.models.proxy.ProcessesProxy');

        if ($processesProxy->instanceActivityControll($id)) {
            $this->logAndMessage( "Errore cancellazione attività \n (non è possibile cancellare un'attività collegata ad un processo)", '', PNX_LOG_ERROR);
            pinax_helpers_Navigation::goHere();
        } else {
            parent::execute($id, $model);
        }

    }
}