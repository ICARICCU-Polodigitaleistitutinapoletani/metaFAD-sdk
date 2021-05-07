<?php

class metafad_mag_controllers_Delete extends metafad_common_controllers_Command
{
    public function execute($id, $model, $reindex = true)
    {
        __Session::remove('idLinkedImages');
        if ($id) {
            $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
            $data = $contentproxy->loadContent($id, $model);

            $this->checkPermissionAndInstitute('delete', $data['instituteKey']);

            $magProxy = pinax_ObjectFactory::createObject('metafad.mag.models.proxy.MagProxy');
            $magProxy->delete($id, $model, false);

            pinax_helpers_Navigation::goHere();
        }
    }

}
