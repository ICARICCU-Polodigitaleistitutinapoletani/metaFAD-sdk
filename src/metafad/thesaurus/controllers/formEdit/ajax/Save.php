<?php

class metafad_thesaurus_controllers_formEdit_ajax_Save extends pinaxcms_contents_controllers_moduleEdit_ajax_Save
{
    function execute($data)
    {
        $decodeData = json_decode($data);
        $thesaurusId = $decodeData->__id;
        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');

        //Prima di tutto salvo (o carico, se già esistente) il dizionario
        $ar = pinax_ObjectFactory::createModel('metafad.thesaurus.models.Thesaurus');
        if ($thesaurusId) {
            $ar->load($thesaurusId);
        }
        $ar->thesaurus_name = $decodeData->thesaurus_name;
        $ar->thesaurus_code = $decodeData->thesaurus_code;
        if (__Config::get('thesaurus.show.keyValue')) {
            $ar->thesaurus_keyValue = $decodeData->thesaurus_keyValue;
        }
        if ($thesaurusId) {
            $ar->thesaurus_modificationDate = new pinax_types_DateTime();
            $ar->save();
        } else {
            $ar->thesaurus_creationDate = new pinax_types_DateTime();
            $ar->thesaurus_modificationDate = new pinax_types_DateTime();
            $thesaurusId = $ar->save();
        }

        //Salvataggio schede collegate e relative voci selezionate
        if ($decodeData->relatedBoardIccd) {
            $oldArForms = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.Forms');
            $oldArForms->select('thesaurusforms_id')->where('thesaurusforms_FK_thesaurus_id', $thesaurusId);
            foreach ($oldArForms as $oldArForm) {
                $arrayOldForms[] = $oldArForm->getRawData()->thesaurusforms_id;
            }
            foreach ($decodeData->relatedBoardIccd as $key => $value) {
                $arrayForms[] = $value->thesaurusFormsId;
            }

            if (is_array($arrayOldForms)) {
                $diff = array_diff($arrayForms, $arrayOldForms);
                $intersect = array_intersect($arrayForms, $arrayOldForms);

                $formsToDelete = array_diff($arrayOldForms, $intersect);

                foreach ($formsToDelete as $formToDelete) {
                    $contentproxy->delete($formToDelete, 'metafad.thesaurus.models.Forms');
                }
            }

            foreach ($decodeData->relatedBoardIccd as $key => $value) {
                $thesaurusFormId = $value->thesaurusFormsId;
                $arForms = pinax_ObjectFactory::createModel('metafad.thesaurus.models.Forms');
                if ($thesaurusFormId) {
                    $arForms->load($thesaurusFormId);
                }
                if ($value->boardName && $value->thesaurusName) {
                    $arForms->thesaurusforms_FK_thesaurus_id = $thesaurusId;
                    $arForms->thesaurusforms_name = $value->boardName->text;
                    $arForms->thesaurusforms_field = $value->thesaurusName->id;
                    $arForms->thesaurusforms_level = $value->boardLevel;
                    if (!is_int($value->boardName->id)) {
                        $arForms->thesaurusforms_moduleId = $value->boardName->id;
                    }

                    if ($thesaurusFormId) {
                        $arForms->thesaurus_modificationDate = new pinax_types_DateTime();
                        $temp = $arForms->save();
                    } else {
                        $arForms->thesaurus_creationDate = new pinax_types_DateTime();
                        $arForms->thesaurus_modificationDate = new pinax_types_DateTime();
                        $thesaurusFormId = $arForms->save();
                    }
                }
            }
        }

        //Il salvataggio delle voci del dizionario viene fatto inline, quindi
        //non si troverà in questo file

        //parent::execute($data);
        die;
        return;
    }
}
