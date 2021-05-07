<?php

class metafad_workflow_processes_controllers_Detail extends pinaxcms_contents_controllers_moduleEdit_Edit
{
     public function execute($id)
    {
        if ($id) {
            $c = $this->view->getComponentById('__model');
            $contentProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
            $data = $contentProxy->loadContent($id, $c->getAttribute('value'));
            $data['processId'] = $id;
            $this->view->setData($data);
        }
    }
}