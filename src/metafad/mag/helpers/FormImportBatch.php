<?php
class metafad_mag_helpers_FormImportBatch extends metafad_jobmanager_service_JobService
{
  public function run()
  {
    set_time_limit(0);
    set_error_handler(array($this, 'errorHandler'));
    $param = $this->params;
    metafad_usersAndPermissions_Common::setInstituteKey($param['instituteKey']);

    try{
      $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);
      $model = $param['model'];
      $start = $param['start'] . ' 00:00:00';
      $end = $param['end'] . ' 23:59:59';
      $data = $param['data'];
      $importMode = $param['importMode'];

      $mappingHelper = pinax_ObjectFactory::createObject('metafad.mag.helpers.MappingHelper',
                        pinax_ObjectFactory::createObject('metafad.mag.models.proxy.DocStruProxy'));
      $it = pinax_ObjectFactory::createModelIterator($model)->
            where('document_detail_modificationDate',$start,'>=')->
            where('document_detail_modificationDate',$end, '<=');
      if($model != 'metafad.sbn.modules.sbnunimarc.model.Model')
      {
        $it->where('instituteKey',$param['instituteKey']);
      }

      $total = $it->count();

      $count = 0;

      if($it->count() == 0){
        $this->setMessage('Attenzione, non ci sono schede importabili per il tipo scelto.');
        $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        $this->save();
        die;
      }
      else {
        foreach ($it as $ar) {
          if ($model == 'metafad.sbn.modules.sbnunimarc.model.Model')
          {
            //SKIP se non ha immagini
            if(!$ar->linkedStruMag && !$ar->linkedMedia && !$ar->linkedInventoryMedia && !$ar->linkedInventoryStrumag)
            {
              continue;
            }
          }
          $mapping = $mappingHelper->getMapping($ar->getRawData(),$data,$model,$ar->document_id);
          if($mapping){
            $id = ($model == 'metafad.sbn.modules.sbnunimarc.model.Model') ? $ar->id : $ar->document_id;
            $mappingHelper->createMag($mapping, $importMode, $model, $id, true);
            $count++;

            $progress = $count * 100 / $total;
            $this->updateProgress($progress);
            $this->save();
          }
          else if($mapping == 'error') {
            $this->setMessage('Attenzione, non è possibile importare il tipo di scheda selezionato. Servizio non disponibile.');
            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
            $this->save();
            die;
          }
        }
      }

      $this->finish();
      $this->setMessage('Creazione MAG eseguita. Totale creati: '.$count .' (quelli scartati non avevano digitale)');
      $this->save();
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

}
