<?php
class metafad_viewer_helpers_FirstImage extends PinaxObject
{
  protected $viewerHelper;

  function execute($id,$type)
  {
    $this->viewerHelper = pinax_ObjectFactory::createObject('metafad.viewer.helpers.ViewerHelper');
    if(!$type)
    {
      return array('error'=>'Parametro "type" non definito');
    }

    if($type == 'sbn') {
      $record = pinax_ObjectFactory::createModelIterator('metafad.sbn.modules.sbnunimarc.model.Model')
        ->where('id',$id)->first();
        if($record->linkedStruMag)
        {
          $strumagId = ($record->linkedStruMag->id) ? : $record->linkedStruMag['id'];
          return $this->getImageFromStruMag($strumagId, $record);
        }
        else if($record->linkedMedia)
        {
          $dam = $this->viewerHelper->initializeDam($this->viewerHelper->getKey($record->linkedMedia[0]->instituteKey));
          if(json_decode($record->linkedMedia[0]->media)->id)
          {
              return array('firstImage' => metafad_dam_Common::replaceUrl($dam->streamUrl(json_decode($record->linkedMedia[0]->media)->id,'thumbnail')));
          }
        }
        else if($record->linkedInventoryMedia)
        {
          $dam = $this->viewerHelper->initializeDam($this->viewerHelper->getKey($record->linkedInventoryMedia[0]->instituteKey));
          return array('firstImage' => metafad_dam_Common::replaceUrl($dam->streamUrl(json_decode($record->linkedInventoryMedia[0]->media[0]->mediaInventory)->id,'thumbnail')));
        }
        else if($record->linkedInventoryStrumag)
        {
          $dam = $this->viewerHelper->initializeDam($this->viewerHelper->getKey($record->linkedInventoryStrumag[0]->instituteKey));
          $strumagId = $record->linkedInventoryStrumag[0]->linkedStruMagToInventory->id;
          $s = $this->getStrumag($strumagId);

          $ps = json_decode($s->physicalSTRU);
          return array('firstImage' => metafad_dam_Common::replaceUrl($dam->streamUrl($ps->image[0]->id,'thumbnail')));
        }
    }
    else if($type == 'iccd'){
      //Ottengo il record, in particolare FTA in caso di scheda ICCD
      //Non invio stru logica in quanto non collego strumag alle iccd
      $record = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
      if($record->load($id)){
        $ar = pinax_ObjectFactory::createModel($record->document_type.'.models.Model');
        $ar->load($id);
        $dam = $this->viewerHelper->initializeDam($this->viewerHelper->getKey($ar->getRawData()->instituteKey));
        $data = $ar->getRawData()->FTA;
        if ($data) {
          foreach ($data as $k => $v) {
            if($v->{"FTA-image"})
            {
              return array('firstImage'=> metafad_dam_Common::replaceUrl($dam->streamUrl(json_decode($v->{"FTA-image"})->id,'thumbnail')));
            }
          }
        }
      }
    }
    else if($type == 'archive')
    {
      $record = pinax_ObjectFactory::createModel('archivi.models.Model');
      $record->setType(null);
      if($record->load($id)){
        $dam = $this->viewerHelper->initializeDam($this->viewerHelper->getKey($record->instituteKey));
        if($record->document_type == 'archivi.models.UnitaDocumentaria' || $record->document_type == 'archivi.models.UnitaArchivistica' || $record->document_type == 'archivi.models.ProgettoDiDigitalizzazione')
        {
          $record = $record->getRawData();
          if($record->linkedStruMag)
          {
            $strumagId = $record->linkedStruMag->id;
            $s = $this->getStrumag($strumagId);

            $ps = json_decode($s->physicalSTRU);
            return array('firstImage' => metafad_dam_Common::replaceUrl($dam->streamUrl($ps->image[0]->id,'thumbnail')));
          }
          else if($record->mediaCollegati)
          {
            return array('firstImage' => metafad_dam_Common::replaceUrl($dam->streamUrl(json_decode($record->mediaCollegati)->id,'thumbnail')));
          }
        }
      }
    }
    else {
      return array('error'=>'Il type indicato non ha corrispondenza');
    }
  }

  public function getImageFromStruMag($strumagId, $record = null)
  {
      $s = $this->getStrumag($strumagId);
      $ps = json_decode($s->physicalSTRU);
      $ik = ($record->linkedStruMag->instituteKey) ? : $record->linkedStruMag['instituteKey'];
      $dam = $this->viewerHelper->initializeDam($this->viewerHelper->getKey($ik));
      return array('firstImage' => metafad_dam_Common::replaceUrl($dam->streamUrl($ps->image[0]->id,'thumbnail')));
  }

  public function getStrumag($id)
  {
    $linkedStru = new stdClass();
    $stru = pinax_ObjectFactory::createModelIterator('metafad.strumag.models.Model')
                 ->where('document_id',$id)->first();

    if($stru)
    {
      $stru->getRawData();
      $linkedStru->physicalSTRU = $stru->physicalSTRU;
      $linkedStru->logicalSTRU = $stru->logicalSTRU;
      return $linkedStru;
    }
    else
    {
      return array('error'=>'Il type indicato non ha corrispondenza');
    }
  }
}
