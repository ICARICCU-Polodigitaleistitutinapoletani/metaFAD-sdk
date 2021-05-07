<?php
class metafad_thesaurus_controllers_ajax_AddTerm extends metafad_common_controllers_ajax_CommandAjax
{
    function execute($fieldName, $model, $query, $term, $proxy, $proxyParams, $getId)
    {
        $result = $this->checkPermissionForBackend('edit');
        if (is_array($result)) {
            return $result;
        }

        $proxyParams = json_decode($proxyParams);

        $thesaurus = pinax_ObjectFactory::createModel('metafad.thesaurus.models.Thesaurus');
        $thesaurusDetail = pinax_ObjectFactory::createModel('metafad.thesaurus.models.ThesaurusDetails');
        if(strtolower($term) === 'altro...') {
            $term = 'altro';
        }

        $existTerm = $thesaurusDetail->find(array('thesaurusdetails_key' => $term,'thesaurus_code' => $proxyParams->code));
        $result = $thesaurus->find(array('thesaurus_code' => $proxyParams->code));

        if ($result && !$existTerm) {
            $arDetails = pinax_ObjectFactory::createModel('metafad.thesaurus.models.Details');
            $arDetails->thesaurusdetails_FK_thesaurus_id = $thesaurus->getId();
            $arDetails->thesaurusdetails_level = $proxyParams->level;
            $arDetails->thesaurusdetails_key = $term;
            $arDetails->thesaurusdetails_value = $term;
            $arDetails->thesaurusdetails_creationDate = new pinax_types_DateTime();
            $arDetails->thesaurusdetails_modificationDate = new pinax_types_DateTime();
            if (__Config::get('metafad.thesaurus.filterInstitute')) {
                $arDetails->thesaurusdetails_instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
            }
            $arDetails->save();
            return true;
        }
    }
}
