<?php
class metafad_usersAndPermissions_institutes_models_proxy_InstitutesProxy extends PinaxObject
{
    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $it = pinax_ObjectFactory::createModelIterator('metafad.usersAndPermissions.institutes.models.Model');

        if ($term != '') {
            $it->where('institute_name', '%'.$term.'%', 'ILIKE');
        }

        $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();

        $application = pinax_ObjectValues::get('org.pinax', 'application');

        if ($instituteKey != '*' && $application->_user->id !== 1) {
            $it->where('institute_key', $instituteKey);
        }

        $result = array();

        foreach($it as $ar) {
            $result[] = array(
                'id' => $ar->getId(),
                'text' => $ar->institute_name,
                'key' => $ar->institute_key
            );
        }

        return $result;
    }

    public function getInstituteVoById($instituteId)
    {
        $ar = pinax_ObjectFactory::createModel('metafad.usersAndPermissions.institutes.models.Model');
        $ar->load($instituteId);
        return $ar->getValues();
    }

    public function getInstituteVoByKey($instituteKey)
    {
        $ar = pinax_ObjectFactory::createModel('metafad.usersAndPermissions.institutes.models.Model');
        $ar->find(array('institute_key' => $instituteKey));
        return $ar->getValues();
    }

    public function getOtherInstitutesList($instituteKey)
    {
        $list = array();
        $it = pinax_ObjectFactory::createModelIterator('metafad.usersAndPermissions.institutes.models.Model')
                ->where('institute_key',$instituteKey,'<>');
        foreach($it as $ar)
        {
            array_push($list, $ar->institute_key);
        }
        return $list;
    }
}