<?php
class metafad_usersAndPermissions_users_controllers_ajax_Save extends pinaxcms_contents_controllers_activeRecordEdit_ajax_Save
{
    public function execute($data)
    {
        $this->directOutput = true;
        $data = json_decode($data);
        $userProxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.users.models.proxy.UsersProxy');
        $beUser = $userProxy->isBEuserGroup($data->user_FK_usergroup_id);

        if ($beUser && empty($data->instituteAndRole)) {
            $result = array(
                'errors' => array('Aggiungi almeno un istituto')
            );

            return $result;
        }
        else if(!$beUser && !empty($data->instituteAndRole))
        {
            $result = array(
                'errors' => array('Un utente del gruppo "Utenti" non può avere un istituto assegnato.')
            );

            return $result;
        }

        if ($data->__id) {
            $ar = pinax_ObjectFactory::createModel('metafad.usersAndPermissions.users.models.Model');
            $ar->load($data->__id);
            if ($ar->user_password !== $data->user_password) {
                $data->user_password = pinax_password($data->user_password);
            }
        } else {
            $data->user_password = pinax_password($data->user_password);
        }

        $result = parent::execute($data);

        $relationsProxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.relations.models.proxy.RelationsProxy');
        $relationsProxy->save($result['set']['__id'], $data->instituteAndRole);

        return $result;
    }
}