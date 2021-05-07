<?php
class metafad_mag_controllers_detail_Download extends metafad_common_controllers_Command
{
  function execute($id)
  {
        $result = $this->checkPermissionForBackend('visible');
        if (is_array($result)) {
            return $result;
        }
        
    if ($id) {
      $exportHelper = pinax_ObjectFactory::createObject('metafad.mag.helpers.ExportHelper',$this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy'));
      $exportHelper->showXML($id);
    }
  }
}
