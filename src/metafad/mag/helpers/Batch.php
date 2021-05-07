<?php
class metafad_mag_helpers_Batch extends metafad_jobmanager_service_JobService
{
  private $docStruProxy;
  private $imagesArray;
  private $damUrl;
  private $originalSizeName;
  private $damPath;
  private $exportHelper;
  private $imagesFolder;
  private $damService;
  private $usageMapping = array('H' => '1','M' => '2', 'S' => '3');

  public function run()
  {
    $application = pinax_ObjectValues::get('org.pinax', 'application');
    set_time_limit(0);
    set_error_handler(array($this, 'errorHandler'));
    $param = $this->params;
    metafad_usersAndPermissions_Common::setInstituteKey($param['instituteKey']);
    $this->docStruProxy = pinax_ObjectFactory::createObject('metafad.mag.models.proxy.DocStruProxy', $application);
    $this->exportHelper = pinax_ObjectFactory::createObject('metafad.mag.helpers.ExportHelper',$this->docStruProxy);
    $this->damService = pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia');
    try{
      $magList = $param['magList'];
      $title = $param['title'];
      $mode = $param['mode'];
      $email = $param['email'];
      $format = $param['format'];

      $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);

      if($mode !== 'oai')
      {
        $exportFolder = __Config::get('metafad.MAG.export.folder') . '/' . md5($title);
        $mkdir = mkdir($exportFolder);
        if(!$mkdir)
        {
          $this->setMessage('Impossibile creare cartella '.$exportFolder.' per export.');
          $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
          $this->save();
          die;
        }
        $this->damUrl = metafad_dam_Common::getDamUrl(metafad_usersAndPermissions_Common::getInstituteKey());
        $this->damPath = __Config::get('metafad.dam.storage.folder');
      }
      $progressStep = $this->calculateProgress(sizeof($magList));
      $progress = 0;

      foreach ($magList as $id) {
        $publicationRoot = $this->docStruProxy->getRootNode($id);
        $this->imagesFolder = array();
        if ($publicationRoot) {
          if($mode === 'oai')
          {
            $xml = $this->generateXML($id, true);
            $this->saveXmlOnSolr($id, $xml);
          }
          else if($mode === 'zip')
          {
            $xml = $this->generateXML($id);
            $magFolder = $exportFolder.'/mag-'.$id;
            if(is_dir($magFolder)){
              $this->rrmdir($magFolder);
            }
            if(mkdir($magFolder))
            {
              $this->exportHelper->createXMLFile($magFolder.'/'.$id.'.xml',$xml);
            }
            $imageFolder = $magFolder.'/Immagini';
            if(!is_dir($imageFolder)){
              if(mkdir($imageFolder))
              {
                foreach ($this->imagesArray as $media) {
                  $this->getImagesFromBytestreams($media,$imageFolder,$format);
                }
              }
            }
          }
        }

        if ($mode !== 'oai')
        {
          $zip = $this->zipPack($exportFolder,$magFolder);
          $this->rrmdir($magFolder);
        }

        $progress += $progressStep;
        $this->updateProgress($progress + $progressStep);
      }

      $this->finish();

      if ($mode !== 'oai') {
        $this->rrmdir($exportFolder);
        $this->exportHelper->sendDownloadEmail($email, $title, new pinax_types_DateTime);
        $this->setMessage('Esportazione eseguita. Un\'email per il download è stata inviata all\'indirizzo fornito.');
      }
      else if($mode === 'oai')
      {
        $this->exportHelper->sendOAIEmail($email, $title, new pinax_types_DateTime);
        $this->setMessage('Esportazione OAI-PMH eseguita.');
      }

    }
    catch(Error $e){
        $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        $this->save();
    }
  }

  public function errorHandler(){
      $error = error_get_last();
      if ($error['type'] === E_ERROR) {
          $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
          $this->save();
          die;
      }
  }

  public function getImagesFromBytestreams($media,$imageFolder,$format)
  {
    $damUrl = $this->damService->mediaUrl($media['id']) . '?bytestream=true';
    $damService = pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia');
    $result = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $damUrl);
    $result->execute();
    $bytestream = json_decode($result->getResponseBody())->bytestream;
    $originalSizeName = $media['size'];
    $bnames = array();

    if($media['altimg'])
    {
      $altimgInfo = $this->processAltimg($media['altimg']);
    }

    foreach ($bytestream as $b) {
      if($b->name !== 'thumbnail')
      {
        $bnames[] = $b->name;
        if($b->name == 'original')
        {
          $b->name = ($media['imggroupID']) ?: $media['usage'][0]->usage_value;
        }
        if($format != 'all' && $format != $b->name)
        {
          continue;
        }
        if($b->name != 'original')
        {
          $b->name = (!$altimgInfo[$this->usageMapping[$b->name]]) ? $b->name : $this->usageMapping[$b->name];
        }
        $streamFolder = $imageFolder.'/'.$b->name;
        if(!in_array($streamFolder,$this->imagesFolder))
        {
          if(mkdir($streamFolder))
          {
            $this->imagesFolder[] = $streamFolder;
          }
        }
        // TODO usare il servizio DAM e togliere da config metafad.DAM.file.folder
        $imageRealPath = $this->damPath.'/'.$b->uri;
        $imageExtension = pathinfo($imageRealPath, PATHINFO_EXTENSION);
        copy($imageRealPath,$streamFolder.'/'.$media['name'].'.'.$imageExtension);
      }
    }

    //Export resize in S
    if(!in_array('S',$bnames))
    {
      $streamFolder = $imageFolder.'/S';
      if (!in_array($streamFolder, $this->imagesFolder)) {
        if (mkdir($streamFolder)) {
          $this->imagesFolder[] = $streamFolder;
        }
      }

      $resizeUrl = $damService->resizeStreamUrlLocal($media['id'], 'original', __Config::get('metafad.dam.resizeStreamS'));
      copy($resizeUrl, $streamFolder . '/' . $media['name'] . '.' . $imageExtension);
    }

  }

  private function processAltimg($altimg)
  {
    $info = array();
    foreach($altimg as $ai)
    {
      if(!$ai->altimg_imggroupID)
      {
        if (!empty($ai->altimg_usage)) {
          $info[current($ai->altimg_usage)->altimg_usage_value] = true;
        }
      }
      else
      {
        $info[$ai->altimg_imggroupID] = true;
      }
    }

    return $info;
  }

  private function rrmdir($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (is_dir($dir."/".$object))
          $this->rrmdir($dir."/".$object);
          else
          unlink($dir."/".$object);
        }
      }
      rmdir($dir);
    }
  }

  private function zipPack($exportFolder,$dirToZip)
  {
    $command = 'cd '.$exportFolder.'; zip -r ' . $exportFolder.'.zip ' . basename($dirToZip);
    $o = pinax_helpers_Exec::exec($command);
  }

  private function createMagData($id, $oai)
  {
    $mag = pinax_ObjectFactory::createModel('metafad.mag.models.Model');
    $mag->load($id,'PUBLISHED_DRAFT');
    $this->imagesArray = array();

    $docstru = $this->docStruProxy->getRootNodeByDocumentId($id);

    $magData = new StdClass;
    $magData->gen = $this->docStruProxy->readContentFromNode($docstru->docstru_id);
    $magData->gen['img_group'] = $this->exportHelper->convertObject($magData->gen['GEN_img_group']);
    $magData->images = array();
    $magData->oai = $oai;

    $it = pinax_ObjectFactory::createModelIterator('metafad.mag.models.Img')
          ->where('docstru_rootId',$docstru->docstru_id)
          ->where('docstru_type','Img');
    foreach ($it as $ar) {
      if($oai)
      {
        //Metto nell'href del file il thumbnail invece che l'indirizzo fisico
        $fileInfo = json_decode($ar->file);
        $fileUrl = $this->damService->streamUrl($fileInfo->mediaId,'thumbnail');
        $ar->file = $fileUrl;
      }
      $magData->images[] = $ar->getRawData();
      $this->imagesArray[] = array('id'=>$ar->dam_media_id,'size'=>$ar->originalSizeName,'name'=>$ar->nomenclature, 'usage'=> $ar->usage,'altimg' => $ar->altimg);
    }

    if ($mag->stru_options === '1' || $mag->stru_options == '2')
    {
      $struData = $this->exportHelper->getStruData($mag->linkedStru);
      if ($struData) {
        $magData->struData = $struData;
      }
    }

    return $magData;
  }

  private function generateXML($id, $oai = false)
  {
    $magData = $this->createMagData($id, $oai);
    $skinClass = pinax_ObjectFactory::createObject('pinax.template.skin.PHPTAL', 'mag.xml', ['/opt/app/metafad/public/static/metafad/template/skins/']);
    $skinClass->set('Component', $magData);
    $output = $skinClass->execute();
    return $output;
  }

  private function calculateProgress($size)
  {
    return (float) 100 / $size;
  }

  private function saveXmlOnSolr($id, $xml)
  {
    //SALVO XML PER OAI-PMH su solr
    $doc = new stdClass();

    $doc->id = 'OAI-'.$id;
    $doc->type_nxs = 'OAI-MAG';
    $doc->xml_only_store = $xml;

    $updateDateTime = new DateTime();
    $doc->update_at_s = $updateDateTime->format('Y-m-d H:i:s');

    $json = array(
      'add' => array(
        'doc' => $doc,
        'boost' => 1.0,
        'overwrite' => true,
        'commitWithin' => 1000
      )
    );

    $postBody = json_encode($json);

    $url = __Config::get('metafad.solr.url');
    $request = pinax_ObjectFactory::createObject(
      'pinax.rest.core.RestRequest',
      $url . 'update/json' . '?wt=json&commit=true',
      'POST',
      $postBody,
      'application/json'
    );

    $request->execute();
  }

}