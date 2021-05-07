<?php

class metafad_mag_controllers_Edit extends metafad_common_controllers_Command
{
    public function execute($id)
    {

        if ($id) {
            // read the module content
            $c = $this->view->getComponentById('__model');
            __Request::set('model', $c->getAttribute('value'));

            __Session::set('idLinkedImages',$id);
            __Session::set('prevState','linkEdit');
            __Session::set('relationType','Fa parte di');
            $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
            $document = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
            $data = $contentproxy->loadContent($id, $c->getAttribute('value'));
            if($data['linkedForm']){
                $this->view->getComponentById('linkedFormContainer')->setAttribute('cssClass', 'linkedFormContainer col-sm-5');
            }
            else{
                $this->view->getComponentById('linkedFormContainer')->setAttribute('cssClass', 'linkedFormContainer col-sm-5 hide');
            }
            $this->checkPermissionAndInstitute('edit', $data['instituteKey']);

            $data['GEN_creation'] = $data['document_creationDate'];
            $data['GEN_lastUpdate'] = $data['document_detail_modificationDate'];
            $this->view->getComponentById('struComponent')->setAttribute('data',json_encode($data['linkedStru']));
            $this->view->getComponentById('struComponent')->setAttribute('stru',json_encode($data['logicalStru']));

//  TODO verifica se il record esiste
            $data['__id'] = $id;
            $this->view->setData($data);
        }
        else
        {
            $this->view->getComponentById('linkedFormContainer')->setAttribute('cssClass', 'linkedFormContainer col-sm-5 hide');
            $this->view->getComponentById('showImagesLink')->setAttribute('visible', false);
        }
    }
}
