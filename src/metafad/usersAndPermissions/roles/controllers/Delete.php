<?php
class metafad_usersAndPermissions_roles_controllers_Delete extends metafad_common_controllers_Command
{
    function execute($model, $id)
    {
        $this->checkPermissionForBackend('delete');

        if ($id) {
            $users = [];
            $it = pinax_ObjectFactory::createModelIterator('metafad.usersAndPermissions.relations.models.Model', 'getUsersForRole', array('params' => array('roleId' => $id)));
            foreach ($it as $ar) {
                $users[] = $ar->user_id;
            }
            if(!empty($users))
            {
                __Session::set('delete', 1);
                pinax_helpers_Navigation::goToUrl(__Link::makeUrl('actionsMVC',['pageId' => 'utenti-e-permessi-ruoli','action' => 'edit','id' => $id]));
            }
            else
            {
                $proxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.roles.models.proxy.RolesContentProxy');
                $proxy->delete($id, $model);
                pinax_helpers_Navigation::goHere();
            }
        }
    }
}