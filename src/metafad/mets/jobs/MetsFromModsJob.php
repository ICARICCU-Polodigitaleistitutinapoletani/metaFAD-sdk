<?php
class metafad_mets_jobs_MetsFromModsJob extends metafad_jobmanager_service_JobService
{
    public function run()
    {
        set_time_limit(0);
        set_error_handler(array($this, 'errorHandler'));
        $param = $this->params;
        metafad_usersAndPermissions_Common::setInstituteKey($param['instituteKey']);

        try {
            //$this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);

            //Recupero id dei mods da importare
            $ids = explode(',',$param['ids']);

            //Inizializzo mapping helper
            $mappingHelper = pinax_ObjectFactory::createObject(
                'metafad.mets.helpers.MappingHelper',
                pinax_ObjectFactory::createObject('metafad.mag.models.proxy.DocStruProxy')
            );

            //Totale schede MODS
            $total = count($ids);

            //Count
            $count = 0;

            //Non può capitare, ma non si sa mai
            if ($total == 0) {
                $this->setMessage('Attenzione, non ci sono schede importabili per il tipo scelto.');
                $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
                $this->save();
                die;
            } else {
                foreach ($ids as $id) {
                    $mods = pinax_ObjectFactory::createModel('metafad.mods.models.Model');
                    if($mods->load($id))
                    {
                        $mapping = $mappingHelper->getMapping($mods->getRawData());
                        if ($mapping) {
                            $mappingHelper->createMets($mapping);
                            $count++;

                            $progress = $count * 100 / $total;
                            $this->updateProgress($progress);
                            $this->save();
                        } else if ($mapping == 'error') {
                            $this->setMessage('Attenzione, non è possibile importare il tipo di scheda selezionato. Servizio non disponibile.');
                            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
                            $this->save();
                            die;
                        }
                    }
                }
            }

            $this->finish();
            $this->setMessage('Creazione METS eseguita. Totale creati: ' . $count);
            $this->save();
        } catch (Error $e) {
            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
            $this->save();
        }
    }

    public function errorHandler()
    {
        $error = error_get_last();
        if ($error['type'] === E_ERROR) {
            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
            $this->save();
            die;
        }
    }

}
