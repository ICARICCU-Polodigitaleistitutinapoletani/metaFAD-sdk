<?php
class metafad_thesaurus_controllers_formEdit_Edit extends metafad_common_controllers_Command
{
    public function execute($id)
    {
        $this->checkPermissionForBackend('edit');
        if(__Config::get('metafad.be.hasDictionaries') === 'demo')
		{
			$this->view->getComponentById('importData')->setAttribute('visible',false);
			$this->view->getComponentById('exportData')->setAttribute('visible',false);
		}

        if ($id) {
            // read the module content
            $c = $this->view->getComponentById('__model');
            $it = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusForms');
            $it->where('thesaurus_id', $id);
            foreach ($it as $ar) {
                if($ar->getRawData()->thesaurusforms_name){
                    if($thesaurusFieldId = $ar->getRawData()->thesaurusforms_field){
                        $document = $this->createModel($thesaurusFieldId, 'metafad.thesaurus.models.Thesaurus');
                        $name = $document->thesaurus_name;
                    }
                    $temp[] = array('thesaurusFormsId' => $ar->thesaurusforms_id, 'boardName' => array('id' => $ar->thesaurusforms_id, 'text' => $ar->thesaurusforms_name), 'thesaurusName' => array('id' => $ar->thesaurusforms_field, 'text' => $ar->thesaurusforms_field . ' - ' .__T($ar->thesaurusforms_field)), 'boardLevel' => $ar->getRawData()->thesaurusforms_level);
                }
                $thesaurusName = $ar->getRawData()->thesaurus_name;
            }

            $th = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.Thesaurus')
                  ->where('thesaurus_id',$id)->first();

            $data['relatedBoardIccd'] = $temp;
            $data['thesaurus_name'] = $th->thesaurus_name;
            $data['thesaurus_code'] = $th->thesaurus_code;
            if (__Config::get('thesaurus.show.keyValue')) {
                $data['thesaurus_keyValue'] = $th->thesaurus_keyValue !== false ? 1 : 0;
            }

//  TODO verifica se il record esiste
            $data['__id'] = $id;
            $this->view->setData($data);
        }
    }

    protected function createModel($id = null, $model)
    {
        $document = pinax_ObjectFactory::createModel($model);
        if ($id) {
            $document->load($id);
        }
        return $document;
    }
}
