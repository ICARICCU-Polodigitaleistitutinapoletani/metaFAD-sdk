<?php
class metafad_gestioneDati_boards_controllers_Normalize extends metafad_common_controllers_Command
{
    public function execute($id, $model)
    {
        //Prendo il record in questione
        $ar = pinax_ObjectFactory::createModel($model);
        if(metafad_common_helpers_LanguageHelper::checkLanguage($model))
        {
            $repeatForOtherLanguage = true;
            $otherLanguage = ($this->application->getEditingLanguageId() === 1) ? 2 : 1;
        }
        $ar->load($id);

        $this->checkPermissionAndInstitute('edit', $ar->instituteKey);

        //Estraggo i dati inerenti la versione di scheda semplificata
        $modelSimpleId = $ar->simpleForm;

        $sAr = pinax_ObjectFactory::createModel('metafad.gestioneDati.schedeSemplificate.models.Model');
        $sAr->load($modelSimpleId);
        //Creo un model per fare il trasferimento
        $modules = pinax_Modules::getModules();

        $m = $modules[$sAr->form->id];
        $originalModel = $m->model;
        $oAr = pinax_ObjectFactory::createModel($originalModel);

        //Estraggo i valori dalla semplificata e li aggiungo alla completa
        $values = $ar->getValuesAsArray(false,true,false,false);
        foreach ($values as $key => $value) {
          if($key != 'simpleForm' || $key != 'document_detail_note' )
          {
            $oAr->$key = $value;
          }
        }
        
        $newId = $this->saveProcedure($oAr, $originalModel);

        if($repeatForOtherLanguage)
        {
            $this->repeatForOtherLanguage($id, $model, $newId ,$otherLanguage);
        }

        //Cancello da DB e SOLR i dati della semplificata
        $evt = array('type' => 'deleteRecord', 'data' => $id);
        $this->dispatchEvent($evt);
        $ar->delete();
        //Redirect a index
        pinax_helpers_Navigation::goHere();
    }

    private function repeatForOtherLanguage($id, $model, $newId, $language)
    {
        $this->application->switchEditingLanguage($language);
        $ar = pinax_ObjectFactory::createModel($model);
        $ar->load($id);

        //Estraggo i dati inerenti la versione di scheda semplificata
        $modelSimpleId = $ar->simpleForm;

        $sAr = pinax_ObjectFactory::createModel('metafad.gestioneDati.schedeSemplificate.models.Model');
        $sAr->load($modelSimpleId);
        //Creo un model per fare il trasferimento
        $modules = pinax_Modules::getModules();

        $m = $modules[$sAr->form->id];
        $originalModel = $m->model;
        $oAr = pinax_ObjectFactory::createModel($originalModel);
        $oAr->document_id = $newId;

        //Estraggo i valori dalla semplificata e li aggiungo alla completa
        $values = $ar->getValuesAsArray(false, true, false, false);
        foreach ($values as $key => $value) {
            if ($key != 'simpleForm' || $key != 'document_detail_note') {
                $oAr->$key = $value;
            }
        }

        //Salvataggio su db
        $this->saveProcedure($oAr, $originalModel);
    }

    public function saveProcedure($oAr, $originalModel)
    {
        //Salvataggio su db
        $newId = $oAr->save(null,false,'DRAFT');
        //preparo i dati per salvataggio SOLR
        $data = $oAr->getRawData();
        $data->__id = $newId;
        $data->__model = $originalModel;


        $cl = new stdClass();
        $it = pinax_ObjectFactory::createModelIterator($originalModel);

        if ($it->getArType() === 'document') {
            $it->setOptions(array('type' => 'PUBLISHED_DRAFT'));
        }

        $it->where('document_id',$newId, 'ILIKE');
        foreach ($it as $record) {
            $cl->className = $record->getClassName(false);
            $cl->isVisible = $record->isVisible();
            $cl->isTranslated = $record->isTranslated();
            $cl->hasPublishedVersion = $record->hasPublishedVersion();
            $cl->hasDraftVersion = $record->hasDraftVersion();
            $cl->document_detail_status = $record->getStatus();
        }

        $data->document = json_encode($cl);

        //Salvo su SOLR
        $data->__commit = true;
        $evt = array('type' => 'insertRecord', 'data' => array('data' => $data, 'option' => array('commit' => true)));
        $this->dispatchEvent($evt);

        return $newId;
    }
}
