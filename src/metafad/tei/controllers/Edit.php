<?php
class metafad_tei_controllers_Edit extends metafad_common_controllers_Command
{
    public function execute($id = null, $sectionType = null, $type = null, $templateID = null, $parentId = null, $status = 'published')
    {
        $editType = ($status == 'published') ? 'edit' : 'editDraft';

        if ($id) {
            // read the module content
            $c = $this->view->getComponentById('__model');
            __Request::set('model', $c->getAttribute('value'));

            $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
            $data = $contentproxy->loadContent($id, $c->getAttribute('value'));
            $data['__id'] = $id;
            $this->checkPermissionAndInstitute($editType, $data['instituteKey']);
            $this->view->setData($data);
            
            if(!$data['isTemplate'] || $data['isTemplate'] != 1){
                $this->setComponentsVisibility('templateTitle', false);
            }

            if ($data['sectionType'] == 'manoscritto-unitario') {
                $this->setComponentsVisibility('sommario', false);
            }

            if ($data['sectionType'] == 'manoscritto-composito') {
                $this->setComponentsVisibility('textualUnits', false);
            }

            $type = $data['type'];
        }
        else {
            $data = array('type' => $type);
            if($parentId){
                $parent = new stdClass();
                $parent->id = intval($parentId);
                $record = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
                if ($record->load($parentId)) {
                    if ($record->getRawData()->title && $record->getRawData()->type) {
                        $parent->text = $record->title . " (" . $record->type . ")";
                    }
                }
                $parent->path = '';
            }
            $data['parent'] = $parent;
            if ($templateID != '0' && $templateID != '') {
                $c = $this->view->getComponentById('__model');
                $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
                $data = $contentproxy->loadContent($templateID, $c->getAttribute('value'));

                $this->checkPermissionAndInstitute($editType, $data['instituteKey']);

                $data['parent'] = $parent;
                if($data['isTemplate']){
                    unset($data['isTemplate']);
                }
                if($data['templateTitle']){
                    unset($data['templateTitle']);
                }
                $this->setComponentsVisibility('templateTitle', false);
            } else if ($templateID == '0') {
                $data['isTemplate'] = 1;
                $this->setComponentsVisibility('templateTitle', true);
            } else{
                $this->setComponentsVisibility('templateTitle', false);
            }

            if ($sectionType) {
                $data['sectionType'] = $sectionType;
            }

            if ($sectionType== 'manoscritto-unitario') {
                $this->setComponentsVisibility('sommario', false);
            }

            if ($sectionType == 'manoscritto-composito') {
                $this->setComponentsVisibility('textualUnits', false);
            }

            if (!$data['pageId']) {
                unset($data['pageId']);
            }

            if ($data['sectionType'] == '') {
                unset($data['sectionType']);
            }

            unset($data['pageId']);
            $this->view->setData($data);
        }
    }
}