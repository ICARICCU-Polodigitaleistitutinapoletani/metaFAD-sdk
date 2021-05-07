<?php
class metafad_usersAndPermissions_roles_controllers_ajax_Save extends pinaxcms_contents_controllers_moduleEdit_ajax_Save
{
    public function execute($data)
    {
        $contentproxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.roles.models.proxy.RolesContentProxy');
        $result = $contentproxy->saveContent(json_decode($data));

        $this->directOutput = true;

        if ($result['__id']) {
            return array('set' => $result);
        }
        else {
            return array('errors' => $result);
        }
    }
}