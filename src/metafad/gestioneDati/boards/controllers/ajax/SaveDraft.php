<?php
class metafad_gestioneDati_boards_controllers_ajax_SaveDraft extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($data)
    {
        $result = $this->checkPermissionAndInstitute('editDraft', $data, 'DRAFT');
        if (is_array($result)) {
            return $result;
        }



        $objData = json_decode($data);

        $isNew = $objData->__id == '';
        $type = explode('.', $objData->__model);

        /**
         * @var $iccdProxy metafad_gestioneDati_boards_models_proxy_ICCDProxy
         */
        $iccdProxy = pinax_ObjectFactory::createObject('metafad.gestioneDati.boards.models.proxy.ICCDProxy');

        $checkUnicita = false;
        if (method_exists($iccdProxy, 'checkUnicita')) {
            $checkUnicita = $iccdProxy->checkUnicita($objData, $type[0]);
        }

        if ($checkUnicita != false) {

            $result = array('callback' => 'controlloUnicita', 'data' => $checkUnicita);
        } else {

            $result = $iccdProxy->saveDraft($objData);
            if ($result['set']) {
                metafad_gestioneDati_boards_Common::logAction($isNew, __Request::get('pageId'), 'editDraft', $result['set']['document'], $objData->__id, current($type));
            }
        }

        $this->directOutput = true;
        return $result;
    }
}
