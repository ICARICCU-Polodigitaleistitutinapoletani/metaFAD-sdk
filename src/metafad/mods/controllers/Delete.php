<?php
class metafad_mods_controllers_Delete extends metafad_common_controllers_Command
{
    public function execute($id, $model)
    {
        $moduleProxy = pinax_ObjectFactory::createObject('metafad.mods.models.proxy.ModuleProxy');
        $data = $moduleProxy->load($id, $model);

        $this->checkPermissionAndInstitute('delete', $data['instituteKey']);

        $moduleProxy->delete($id, $model);

        pinax_helpers_Navigation::goHere();
    }
}
