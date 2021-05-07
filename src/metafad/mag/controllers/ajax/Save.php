<?php
class metafad_mag_controllers_ajax_Save extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($data)
    {
        $result = $this->checkPermissionAndInstitute('publish', $data);
        if (is_array($result)) {
            return $result;
        }

        $magProxy = pinax_ObjectFactory::createObject('metafad.mag.models.proxy.MagProxy');
        $objData = json_decode($data);
        $isNew = $objData->__id == '';
        $result = $magProxy->save($objData);

        if ($result['set']) {
            metafad_gestioneDati_boards_Common::logAction($isNew, __Request::get('pageId'), 'edit', $result['set']['document'], $objData->__id, 'scheda MAG');
        }

        $this->directOutput = true;
        return $result;
    }
}
