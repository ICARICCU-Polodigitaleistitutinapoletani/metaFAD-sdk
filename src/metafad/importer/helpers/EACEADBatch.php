<?php

/**
 * Class metafad_importer_helpers_EACEADBatch
 */
class metafad_importer_helpers_EACEADBatch extends metafad_importer_helpers_AbstractBatch
{
    protected $importationsNumber = 0;
    protected $currentNumber = 0;

    /**
     * Nota bene: progress è un numero da 0 a 100
     * @param $evt
     */
    public function mainRunnerProgress($evt)
    {
        if (property_exists($evt, "data") && key_exists("progress", $evt->data)) {
            $p = $evt->data['progress'];
            $t = $this->importationsNumber;
            $c = $this->currentNumber;
            $evt->data['progress'] = ($t ? ($c * 100 + $p) / $t : 100);
        }

        parent::mainRunnerProgress($evt);
    }

    public function run()
    {
        parent::run();

        $log = pinax_log_LogFactory::create('DB', array(), 255, '*');
        $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);
        try {
            $param = $this->params;

            $zipFolder = $param['zipFolder'];
            $zipFile = $param['zipFile'];
            $file_path = $param['filePath'];
            $instance = $param['instance'];
            $format = $param['format'];
            $logFile = $param['logFile'];
            $partialValidation = $param['partialValidation'];
            $onlyValidation = $param['onlyValidation'];
            $overwriteScheda = $param['overwriteScheda'];
            $recordId = $param['recordId'];
            $onlyRecord = $param['onlyRecord'];
            $onlyMedia = $param['onlyMedia'];

            metafad_usersAndPermissions_Common::setInstituteKey($instance);

            if (!@copy($file_path, $zipFile)) {
                $this->setJobMessage();
                @unlink($file_path);
            }

            $this->extractZip($zipFolder, $zipFile);

            $files = $this->rearrangeFiles($zipFolder, $format);
            if ($format === 'ead3' && $files['configFile'] === '') {
                $this->setMessage("Importazione non riuscita: nel pacchetto ZIP manca il file di configurazione.");
                @unlink($zipFolder . '.zip');
                pinax_helpers_Files::deleteDirectory($zipFolder);
                $this->finish();
                $this->save();
                return;
            }
            $this->setMessage("Lettura ZIP completata");
            $this->updateProgress(1);
            array_map(function ($a) use ($log) {
                $log->debug("File '$a' ignorato: formato non riconosciuto");
            }, $files['rejected']);

            $this->currentNumber = 0;
            if ($format === 'eadeac') {
                $this->importationsNumber = count($files['eac']) + count($files['ead']);
                array_map(function ($a) use ($instance, $log, &$progress) {
                    metafad_importer_services_xmlArchiveEADEAC_Importers::importEAC($a, $instance, null, $log);
                    $this->currentNumber++;
                }, array_merge($files['eac'], $files['ead']));
            } elseif ($format === 'ead3' && !count($files['rejected'])) {
                $this->importationsNumber = count($files['ead3']);
                array_map(function ($a) use ($instance, $log, &$progress, $files, $logFile, $partialValidation, $overwriteScheda, $onlyValidation, $recordId, $onlyRecord, $onlyMedia) {
                    metafad_importer_services_xmlArchiveEADEAC_Importers::importEAD($a, $instance, null, $log, false, true, $files['configFile'], $logFile, $partialValidation, $overwriteScheda, $onlyValidation, $recordId, $onlyRecord, $onlyMedia);
                    $this->currentNumber++;
                }, $files['ead3']);
            }

            @unlink($zipFolder . '.zip');
            if (metafad_usersAndPermissions_Common::getInstituteKey() !== 'sias' && substr(metafad_usersAndPermissions_Common::getInstituteKey(), 0, 4) !== 'sias') {
                //pinax_helpers_Files::deleteDirectory($zipFolder);
            } else {
                unlink($files['ead3'][0]);
            }
            $this->finish();
            $this->setJobMessage($logFile, $onlyValidation, $files['rejected']);
            $this->save();
        } catch (Exception $e) {
            $this->logThrowable($e, $log);
        } catch (Error $e) {
            $this->logThrowable($e, $log);
        }
    }

    private function extractZip($zipFolder, $zipFile)
    {
        $fileList = array();
        //Estraggo file da archivio
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {

            for ($i = 0; $i < $zip->numFiles; $i++) {
                if (substr($zip->getNameIndex($i), -1) !== '/') {
                    $extractedFile = $zipFolder . '/' . $zip->getNameIndex($i);
                    if (pathinfo($extractedFile, PATHINFO_EXTENSION) == 'xml' && pathinfo($extractedFile, PATHINFO_BASENAME) != 'geoInfo.xml' && pathinfo($extractedFile, PATHINFO_BASENAME) != 'IMMFTAN.xml' && pathinfo($extractedFile, PATHINFO_BASENAME) != 'INFORMA.xml') {
                        $fileList[pathinfo($extractedFile, PATHINFO_BASENAME)] = $extractedFile;
                    }
                }
            }
            $zip->extractTo($zipFolder);
            $zip->close();
        }
    }

    private function rearrangeFiles($zipFolder, $format)
    {
        $eac = array();
        $ead = array();
        $ead3 = array();
        $rejected = array();
        $configFile = '';

        $files = glob("$zipFolder/*");

        foreach ($files as $file) {
            $xml = new DOMDocument();
            try {
                if (substr($file, -4) != '.xml') {
                    continue;
                }
                @$xml->loadXML(file_get_contents($file));
                $xp = new DOMXPath($xml);
                if ($format === 'eadeac') {
                    if ($xp->query("//rsp/dsc/c")->length > 0) {
                        $ead[] = $file;
                    } else if ($xp->query("//xw_doc/eac-cpf")->length > 0) {
                        $eac[] = $file;
                    } else {
                        $rejected[] = $file;
                    }
                } elseif ($format === 'ead3') {
                    $xp->registerNamespace('icar-import', 'http://www.san.beniculturali.it/icar-import');
                    if ($xp->query("//icar-import:systemId")->length > 0) {
                        $ead3[] = $file;
                    } elseif (($xp->query("/root/ead3")->length > 0)) {
                        $configFile = $file;
                    } else {
                        $rejected[] = $file;
                    }
                }
            } catch (Exception $ex) {
                $rejected[] = $file;
            }
        }
        if ($format == 'eadeac') {
            return array(
                "eac" => $eac,
                "ead" => $ead,
                "rejected" => $rejected
            );
        } elseif ($format == 'ead3') {
            return array(
                "ead3" => $ead3,
                "rejected" => $rejected,
                "configFile" => $configFile
            );
        }
    }

    /**
     * @param $e Throwable
     * @param $log pinax_log_LogBase
     */
    private function logThrowable($e, $log)
    {
        $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        $message = json_encode(metafad_common_helpers_ImporterCommons::getThrowableString($e), JSON_PRETTY_PRINT);
        $this->setMessage($e->getMessage());
        $log->debug($message);
        $this->save();
    }

    private function setJobMessage($logFile = '', $onlyValidation = false, $rejected = [], $copyError = false)
    {
        if ($copyError) {
            $this->setMessage('Errore nel caricamento del pacchetto zip');
            return;
        }
        if (count($rejected) > 0) {
            $this->setMessage('Importazione fallita: schema del file XML non riconosciuto');
            return;
        }
        $path = pinax_Paths::get('ROOT') . 'export/icar-import_log_folder/';
        if (!$onlyValidation) {
            if (file_exists($path . $logFile)  || file_exists($path . md5($logFile) . '_logError.log')) {
                $this->setMessage('Importazione NON eseguita: validazione fallita');
            } else {
                $this->setMessage('Importazione eseguita');
            }
        } else {
            if (file_exists($path . $logFile) || file_exists($path . md5($logFile) . '_logError.log')) {
                $this->setMessage('Validazione fallita');
            } else {
                $this->setMessage('Validazione eseguita con successo');
            }
        }
    }
}
