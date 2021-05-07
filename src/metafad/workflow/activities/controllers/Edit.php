<?php

class metafad_workflow_activities_controllers_Edit extends pinaxcms_contents_controllers_moduleEdit_Edit
{
    public function execute($id)
    {
        if ($id) {
            $c = $this->view->getComponentById('__model');
            $contentProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
            $data = $contentProxy->loadContent($id, $c->getAttribute('value'));
            if(!$data['lock'] || $data['lock'] == 'false'){
                unset($data['lock']);
            }
            $data['__id'] = $id;
            $this->view->setData($data);
            }
        }

}