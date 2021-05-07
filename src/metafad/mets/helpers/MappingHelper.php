<?php
class metafad_mets_helpers_MappingHelper extends PinaxObject
{
  use metafad_common_helpers_MagMetsHelperTrait;
  
  private $docStruProxy;
  private $metsProxy;
  private $images;

  function __construct($docStruProxy)
  {
    $this->docStruProxy = $docStruProxy;
    $this->metsProxy = pinax_ObjectFactory::createObject('metafad_mets_models_proxy_MetsProxy');
  }

  public function getMapping($record)
  {
    $appoggioMets = new stdClass();

    //Recupero i dati del MODS per la sezione apposita in METS
    $modsToMetsData = $this->getMappedField($record);
    $appoggioMets->modsToMetsData = $modsToMetsData;

    //Collego la stru se presente al mets
    if ($record->linkedStruMag) {
      $appoggioMets->linkedStru = $this->getLinkedStruMag($record->linkedStruMag);
    } else if ($record->linkedMedia) {
      $images = array();
      foreach ($record->linkedMedia as $r) {
        $images[] = $r->media;
      }
      $appoggioMets->images = $images;
    }

    return $appoggioMets;
  }

  private function getMappedField($record)
  {
    $fieldsList = array(
      'identificativo-rep',
      'titolo', 'complementoTitolo', 'numeroParteTitolo',
      'nomeParte', 'autore-rep', 'luogo',
      'editore', 'date', 'lingua-rep',
      'materia-rep', 'tecnica-rep', 'tipoEstensione',
      'abstract', 'tavolaContenuti', 'soggetto-rep',
      'classificazione', 'titoloCollegato-rep', 'parte-rep',
      'localizzazione', 'collocazione-rep', 'compilatore',
      'dataCreazione', 'dataModifica'
    );
    $modsFields = array();
    foreach ($fieldsList as $f) {
      $value = $record->$f;
      if ($value && !empty($value)) {
        $modsFields[$f] = $value;
      }
    }
    return $modsFields;
  }

  public function createMets($mapping)
  {
    $StruMagProxy = pinax_ObjectFactory::createObject('metafad.strumag.models.proxy.StruMagProxy');
    $document = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');

    //Rimozione duplicati
    $dc_identifier = ($mapping->modsToMetsData['identificativo-rep'][0]->identificativo) ? : null;
    if ($dc_identifier) {
      $it = pinax_ObjectFactory::createModelIterator('metafad.mets.models.Model')
        ->where('identifier', $dc_identifier);
      if ($it->count() > 0) {
        foreach ($it as $ar) {
          $this->deleteMets($ar->document_id, 'metafad.mets.models.Model');
        }
      }
    }

    //Dati sezione mods
    $mets = pinax_ObjectFactory::createModel('metafad.mets.models.Model');
    $mets->mods = array();
    $mods = new stdClass();
    foreach ($mapping->modsToMetsData as $key => $value) {
      $mods->$key = $value;
    }
    $mets->mods = array($mods);

    //Date
    $date = new pinax_types_DateTime();

    //Identifier
    $mets->identifier = $dc_identifier;
    $mets->dc = array();
    $bib = new stdClass();
    $bibValue = new stdClass();
    $bibValue->BIB_dc_identifier_value = $dc_identifier;
    $bib->BIB_dc_identifier = array($bibValue);
    $mets->dc = array($bib);

    //Linked strumag
    if ($mapping->linkedStru)
    {
      $mets->linkedStru = $mapping->linkedStru;
    }

    //Salvataggio
    $decodeData = $mets->getRawData();
    $decodeData->__commit = true;
    $decodeData->__model = 'metafad.mets.models.Model';
    $result = $this->metsProxy->save($decodeData);
    $id = $result['id'];

    //Salvataggio eventuali immagini
    if ($mapping->images) {
      $this->docStruProxy->createPages($result['rootId'], $mapping->images, 'mets');
    }
  }

  public function deleteMets($id, $model)
  {
    if ($id) {
      $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
      $struMagProxy = pinax_ObjectFactory::createObject('metafad.strumag.models.proxy.StruMagProxy');
      $rootNode = $this->docStruProxy->getRootNodeByDocumentId($id);
      $this->docStruProxy->deleteNode($rootNode->docstru_id);

      $tmp = $contentproxy->loadContent($id, $model);
      $idStruMag = json_decode($tmp['relatedStru'])->id;
      if ($idStruMag) {
        $doc = new stdClass();
        $doc->MAG = "";
        $struMagProxy->modify($idStruMag, $doc);
      }

      $contentproxy->delete($id, $model);

      $evt = array('type' => 'deleteRecord', 'data' => $id);
      $this->dispatchEvent($evt);
    }
  }

}
