<?php
class metafad_strumag_rest_controllers_Save extends pinax_rest_core_CommandRest
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute($id)
    {
        $this->checkPermissionForBackend();
        $isNew = (!$id) ? true : false;
        $body = json_decode(__Request::getBody());
        $now = new pinax_types_DateTime();
        $ar = pinax_ObjectFactory::createModel('metafad.strumag.models.Model');
        $ar->load($id);
        $ar->state = $body->state;
        $ar->title = $body->title;
        $ar->physicalSTRU = json_encode($body->physicalSTRU);
        $ar->logicalSTRU = json_encode($body->logicalSTRU);
        $id = $ar->publish();

        $decodeData = (object)$ar->getValuesAsArray();

        $cl = new stdClass();

        $it = pinax_ObjectFactory::createModelIterator( 'metafad.strumag.models.Model' );

        if ($it->getArType() === 'document') {
            $it->setOptions(array('type' => 'PUBLISHED_DRAFT'));
        }

        $it->where('document_id', $id, 'ILIKE');
        foreach ($it as $record) {
            $cl->className = $record->getClassName(false);
            $cl->isVisible = $record->isVisible();
            $cl->isTranslated = $record->isTranslated();
            $cl->hasPublishedVersion = $record->hasPublishedVersion();
            $cl->hasDraftVersion = $record->hasDraftVersion();
            $cl->document_detail_status = $record->getStatus();
        }

		$decodeData->physicalSTRU = $ar->physicalSTRU;
		$decodeData->logicalSTRU = $ar->logicalSTRU;

		$decodeData->__id = $id;
        $decodeData->__model = 'metafad.strumag.models.Model';
        $decodeData->instituteKey = $ar->instituteKey ?: metafad_usersAndPermissions_Common::getInstituteKey();
        $decodeData->document = json_encode($cl);

        metafad_gestioneDati_boards_Common::logAction($isNew, 'teca-STRUMAG', 'edit', $ar, $id, 'STRUMAG');

        $decodeData->__commit = true;
        $evt = array('type' => 'insertRecord', 'data' => array('data' => $decodeData, 'option' => array('commit' => true)));
        $this->dispatchEvent($evt);

        $vo = pinax_ObjectFactory::createObject('metafad_strumag_models_vo_STRUMAGVO', $ar);
        return $vo;
    }
}
