<?php
class metafad_usersAndPermissions_users_controllers_Delete extends pinaxcms_contents_controllers_activeRecordEdit_Delete
{
    public function execute($id, $model)
    {
        $relationsProxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.relations.models.proxy.RelationsProxy');
        $relationsProxy->delete($id);

        parent::execute($id, $model);
    }
}