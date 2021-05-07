<?php
class metafad_strumag_controllers_Delete extends metafad_common_controllers_Command
{
    public function execute($id, $model)
    {
        $this->checkPermissionForBackend('delete');

        if ($id) {
            $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
            $data = $contentproxy->loadContent($id, $model);

            $this->checkPermissionAndInstitute('delete', $data['instituteKey']);

            $struMagProxy = pinax_ObjectFactory::createObject('metafad.strumag.models.proxy.StruMagProxy');
            $struMagProxy->delete($id);

            pinax_helpers_Navigation::goHere();
        }
    }
}
