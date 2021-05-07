<?php
class metafad_importer_controllers_ajax_Import extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($data)
    {
        $result = $this->checkPermissionForBackend('edit');

        if (__Config::get('metafad.be.hasImport') === 'demo') {
            $this->logAndMessage(__T('Spiacente, questa funzionalità non è disponibile nella modalità DEMO.'), '', PNX_LOG_INFO);
            $url = pinax_helpers_Link::makeUrl('link', array('pageId' => 'metafad.importer'));
            return array('url' => $url);
        }

        if (is_array($result)) {
            return $result;
        }

        $data = json_decode($data);
        $files = $data->medias;
        $this->directOutput = true;

        if ($data->format == 'sbn') {
            if ($data->module == 'gestione-dati/authority/sbn') {
                if (!$data->sbnAutFolder) {
                    return array('errors' => array('error' => 'Attenzione, selezionare una cartella contenente dati SBN AUT.'));
                } else {
                    $this->createSBNJob('sbnaut', $data->sbnAutFolder, $data->uploadType);
                    $url = pinax_helpers_Link::makeUrl('link', array('pageId' => 'metafad.jobsReport'));
                    return array('url' => $url);
                }
            } else if ($data->module == 'metafad.sbn.modules.sbnunimarc') {
                if (!$data->sbnFolder) {
                    return array('errors' => array('error' => 'Attenzione, selezionare una cartella contenente dati SBN Unimarc.'));
                } else {
                    $this->createSBNJob('sbn', $data->sbnFolder, $data->uploadType);
                    $url = pinax_helpers_Link::makeUrl('link', array('pageId' => 'metafad.jobsReport'));
                    return array('url' => $url);
                }
            } else {
                return array('errors' => array('error' => 'Attenzione, il tipo di scheda scelto non può essere importato nel formato richiesto.'));
            }
        } else if ($files->__uploadFilename[0]) {
            $file_path = $files->__uploadFilename[0];
            $file_name = $files->__originalFileName[0];
            return $this->import($data->module, $data->format, $file_path, $file_name, $data->overwriteScheda, $data->overwriteAuthority, true, $data->partialValidation, $data->onlyValidation, $data->recordId, $data->onlyRecord, $data->onlyMedia);
        } else if ($data->fileFromServer) {
            $file_path = __Config::get('metafad.importer.storageFolder') . $data->fileFromServer;
            $fileSplit = explode('/', $data->fileFromServer);
            $file_name = array_pop($fileSplit);
            return $this->import($data->module, $data->format, $file_path, $file_name, $data->overwriteScheda, $data->overwriteAuthority, false, $data->partialValidation, $data->onlyValidation, $data->recordId, $data->onlyRecord, $data->onlyMedia);
        } else {
            return array('errors' => array('error' => 'Selezionare un file (o attenderne l\'upload su server).'));
        }
    }

    protected function import($module, $format, $file_path, $file_name, $overwriteScheda, $overwriteAuthority, $unlinkFilePath = false, $partialValidation = false, $onlyValidation = false, $recordId = null, $onlyRecord = false, $onlyMedia = false)
    {
        $uploadFolder = __Config::get('metafad.importer.uploadFolder');
        $zipFile = $uploadFolder . '/' . $file_name;
        $zipFolder = str_ireplace('.zip', '', $zipFile);

        @mkdir($zipFolder, 0777, true);

        if ($format != 'eadeac' && $format != 'ead3') {
            if (!@copy($file_path, $uploadFolder . '/' . $file_name)) {
                throw new Exception('Errore nel caricamento del file:' . $file_name);
                @unlink($file_path);
            }
        }

        if ($unlinkFilePath && $format != 'eadeac' && $format != 'ead3') {
            @unlink($file_path);
        }

        if ($format == "trc") {
            $fileList = array();
            //Estraggo file da archivio
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    if (substr($zip->getNameIndex($i), -1) !== '/') {
                        $extractedFile = $zipFolder . '/' . $zip->getNameIndex($i);
                        if (pathinfo($extractedFile, PATHINFO_EXTENSION) == '' || is_numeric(pathinfo($extractedFile, PATHINFO_EXTENSION))) {
                            $fileList[pathinfo($extractedFile, PATHINFO_BASENAME)] = $extractedFile;
                        }
                    }
                }
                $zip->extractTo($zipFolder);
                $zip->close();
            }

            //Array appoggio importazione
            $arrayAUTBIB = array();
            $arrayForm = array();
            $types = array();
            foreach ($fileList as $k => $f) {
                $content = preg_replace('/\s+/', " ", file($f));
                $intestazione = explode(" ", $content[0]);
                $identifier = $intestazione[0];
                $matches = array();

                if (preg_match('/^(.{4})(\w)(\w+\d+|ICCD)(\w+\d*)/i', $identifier, $matches)) {
                    $type = $matches[4];
                    $version = str_replace(".", "", $matches[1]);
                    $moduleName = "$type$version";

                    $types[] = strtolower($moduleName . '_alias');
                    $types[] = strtolower($moduleName);
                    $types[] = strtolower("scheda" . $moduleName . '_alias');
                    $types[] = strtolower("scheda" . $moduleName);
                    $modelPath = "$moduleName.models.Model";
                } else {
                    throw new Exception("Formato non riconosciuto nel file $f. Ricontrollarne la prima riga che dev'essere conforme allo standard ICCD 92.");
                }

                if ($type === 'AUT' || $type === 'BIB') {
                    $arrayAUTBIB[] = array('file' => $k, 'type' => $type, 'version' => $version, 'moduleName' => $moduleName, 'modelPath' => $modelPath);
                } else {
                    $arrayForm[] = array('file' => $k, 'type' => $type, 'version' => $version, 'moduleName' => 'Scheda' . $moduleName, 'modelPath' => 'Scheda' . $modelPath);
                }
            }
        } else if ($format == "iccdxml") {
            $fileList = $this->extractZip($zipFolder, $zipFile);

            //Array appoggio importazione
            $arrayAUTBIB = array();
            $arrayForm = array();
            $types = array();

            foreach ($fileList as $k => $f) {

                $version = "";
                $type = "";
                $this->checkTypeVer($f, $version, $type);

                $version = str_replace(".", "", $version);
                $moduleName = "$type$version";
                // TODO togliere prefisso scheda dai moduli
                $types[] = strtolower('scheda' . $moduleName . '_alias');
                $types[] = strtolower('scheda' . $moduleName);
                $types[] = strtolower($moduleName . '_alias');
                $types[] = strtolower($moduleName);
                $modelPath = "$moduleName.models.Model";
                //echo "ZZ".$modelPath."\n";
                if ($type === 'AUT' || $type === 'BIB') {
                    $arrayAUTBIB[] = array('file' => $k, 'type' => $type, 'version' => $version, 'moduleName' => $moduleName, 'modelPath' => $modelPath);
                } else {
                    $arrayForm[] = array('file' => $k, 'type' => $type, 'version' => $version, 'moduleName' => 'Scheda' . $moduleName, 'modelPath' => 'Scheda' . $modelPath);
                }
            }
        } else if ($format == "tei" || $format == "eadeac" || $format == "ead3") {
            if ($format != "eadeac" && $format != "ead3") {
                $fileList = $this->extractZip($zipFolder, $zipFile);
            }
            $types = array();
            $types[] = $module;
        }

        //Se la scheda indicata come da importare non è presente
        if (!in_array($module, $types)) {
            @unlink($zipFile);
            pinax_helpers_Files::deleteDirectory($zipFolder);
            return array('errors' => array('error' => 'Il tipo di scheda scelta non è presente nell\'archivio caricato.'));
        }

        $params = array(
            'format' => $format,
            'arrayAUTBIB' => $arrayAUTBIB,
            'arrayForm' => $arrayForm,
            'zipFolder' => $zipFolder,
            'zipFile' => $zipFile,
            'filePath' => $file_path,
            'instance' => metafad_usersAndPermissions_Common::getInstituteKey(),
            'overwriteScheda' => $overwriteScheda,
            'overwriteAuthority' => $overwriteAuthority,
            'logFile' => uniqid(rand()) . uniqid() . '.log',
            'partialValidation' => $partialValidation,
            'onlyValidation' => $onlyValidation,
            'onlyRecord' => $onlyRecord,
            'onlyMedia' => $onlyMedia
        );

        if (!is_null($recordId)) {
            $params['recordId'] = $recordId;
        }

        if ($params['instance']) {
            $jobClassName = ($params['format'] == 'eadeac' || $params['format'] == 'ead3') ? 'metafad.importer.helpers.EACEADBatch' : 'metafad.importer.helpers.Batch';

            //Creazione del job di importazione
            $jobFactory = pinax_ObjectFactory::createObject('metafad.jobmanager.JobFactory');
            $jobFactory->createJob(
                $jobClassName,
                $params,
                'Importazione pacchetto ' . $file_name,
                'BACKGROUND'
            );
            $url = pinax_helpers_Link::makeUrl('link', array('pageId' => 'metafad.jobsReport'));
            return array('url' => $url);
        } else {
            return array('errors' => array('error' => 'La selezione dell\'istituto è mancante. Potrebbe essere scaduta la sessione. Contattare l\'amministratore di sistema.'));
        }
    }

    protected function extractZip($zipFolder, $zipFile)
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

        return $fileList;
    }

    private function createSBNJob($format, $folder, $uploadType)
    {
        $jobFactory = pinax_ObjectFactory::createObject('metafad.jobmanager.JobFactory');
        $jobFactory->createJob(
            'metafad.importer.services.sbn.Batch',
            array(
                'format' => $format,
                'folder' => $folder,
                'uploadType' => $uploadType
            ),
            'Importazione ' . $folder,
            'BACKGROUND'
        );
    }

    private function checkTypeVer($file, &$version, &$type)
    {
        $xmlDoc = new DOMDocument();
        $xmlDoc->load($file);
        $xpath = new DOMXpath($xmlDoc);
        $elements = $xpath->query("/csm_root/csm_info/nome_normativa");
        $type = $elements->item(0)->nodeValue;
        $elements = $xpath->query("/csm_root/csm_info/ver_numero");
        $version = $elements->item(0)->nodeValue;
    }
}
