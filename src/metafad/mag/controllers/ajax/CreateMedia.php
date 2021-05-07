<?php

set_time_limit(0);

class metafad_mag_controllers_ajax_CreateMedia extends metafad_common_controllers_ajax_CommandAjax
{
  use metafad_common_traits_CreateMediaTrait;

  public function execute($type = null, $stru = null, $key = null, $id = null, $physical = false)
  {
    $result = $this->checkPermissionForBackend('edit');
    if (is_array($result)) {
      return $result;
    }

    //Estraggo informazioni su docstru
    $docStruProxy = $this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy');
    $rootId = $docStruProxy->getRootNodeByDocumentId($id);

    $it = pinax_ObjectFactory::createModelIterator('metafad.mag.models.Img')
      ->where('docstru_rootId', $rootId->docstru_id)
      ->where('docstru_type', 'Img');

    if ($it->count() > 0) {
      $ar = $it->first();
      $docStruProxy->deleteNode($ar->docstru_rootId, true, 'MAG', false);
    }

    foreach ($it as $ar) {
      $ar->delete();
    }

    $it = pinax_ObjectFactory::createModelIterator('metafad.strumag.models.Model')
      ->where('document_id', $stru)->first();
    $logicalStru = json_decode($it->logicalSTRU);
    $physicalStru = json_decode($it->physicalSTRU);

    //Estraggo informazioni su docstru
    $docStruProxy = $this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy');
    $rootId = $docStruProxy->getRootNodeByDocumentId($id);
    //Estraggo tutti gli id dei nodi che voglio salvare
    //Se $key è settato devo escludere tutti i nodi che non ne sono figli
    $idList = array();
    $struToImport = array();
    if ($key) {
      foreach ($key as $v) {
        $struToImport[] = $this->exploreLogicalStruWithKey($logicalStru, $v);
      }
      $this->getElementsId($struToImport, $idList);
    } else {
      $this->getElementsId($logicalStru, $idList);
    }

    //Cerco i media da collegare in base al confronto di $e->keyNode con $idList
    if (empty($idList) && $physical == 'true') {
      foreach ($physicalStru as $elements) {
        //Ogni elements è un array con un singolo tipo di media
        $count = 1;
        foreach ($elements as $e) {
          $docStruProxy->saveNewMedia($e, $rootId->docstru_id, $count);
          $count++;
        }
      }
    } else {
      foreach ($physicalStru as $elements) {
        //Ogni elements è un array con un singolo tipo di media
        $count = 1;
        foreach ($elements as $e) {
          if (in_array($e->keyNode, $idList)) {
            //TODO leggere $e->metadata per relativi metadati precompilati da DAM
            $docStruProxy->saveNewMedia($e, $rootId->docstru_id, $count);
          }
          if ($e->keyNode != 'exclude') {
            $count++;
          }
        }
      }
    }

    return array('sendOutput' => 'fileTabs', 'sendOutputState' => 'edit', 'sendOutputFormat' => 'html');
  }
}
