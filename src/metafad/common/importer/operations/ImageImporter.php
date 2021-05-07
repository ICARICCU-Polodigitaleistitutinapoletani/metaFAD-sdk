<?php

/**
 * Salva l'input nel database usando il proxy dell'archivistico
 */
class metafad_common_importer_operations_ImageImporter extends metafad_common_importer_operations_LinkedToRunner
{
    private $filename = false;
    private $dom;
    private $completeDom;
    private $xpathComplete;
    private $xpath;
    private $daoPaths;
    private $dam;
    private $tableDao;
    private $tableMets;
    private $tableTitle;
    private $tableMagsFile;
    private $tableModel;
    private $tableIdentifier;
    private $uploadFolder;
    private $importFolder;
    private $arcProxy;
    private $application;
    private $event;
    private $overwrite;
    private $metsXpath;
    private $instituteKey;
    private $fileLogger;
    private $warningFile;
    private $importMedia;


    function __construct($params, $runner)
    {
        $this->overwrite = $params->overwriteScheda == '1';
        $this->filename = $params->filename ?: $this->filename;
        $this->daoPaths = json_decode(file_get_contents($params->daoFile));
        $this->dam = pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia', $params->instituteKey);
        $this->uploadFolder = $this->createUploadFolder();
        $this->importFolder = $this->buildImportFolderPath();
        $this->arcProxy = pinax_ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
        $this->arcProxy->isImportProcess();
        $this->application = pinax_ObjectValues::get('org.pinax', 'application');
        $this->event = pinax_ObjectFactory::createObject('metafad_mag_services_Event', $this->application);
        $this->tableDao = [];
        $this->tableMets = [];
        $this->tableMagsFile = [];
        $this->tableTitle = [];
        $this->tableIdentifier = [];
        $this->instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
        $warningFile = "./export/icar-import_log_folder/" . md5($params->logFile);
        $this->fileLogger = pinax_ObjectFactory::createObject('metafad_common_importer_utilities_ImportFileLogger', $warningFile);
        $this->importMedia = $params->importMedia == '0';
        parent::__construct($params, $runner);
    }

    function execute($input)
    {
        set_time_limit(0);
        if (!$this->importMedia) {
            return $input;
        }
        $docs = $this->readEad();
        foreach ($docs as $doc) {
            $this->dom = '';
            $this->tableDao = [];
            $this->tableMets = [];
            $this->tableMagsFile = [];
            try {
                $this->dom = $doc;
                $this->xpath = new DOMXPath($this->dom);
            } catch (Exception $ex) {
                if (!$this->suppressErrors) {
                    throw $ex;
                }
            }
            $this->mapHref();
            $this->importImg($this->tableDao);
            $this->importImg($this->tableMets);
            $this->importMag($this->tableMagsFile);
            $this->cleanImportFolder(true);
            if ($this->instituteKey == 'diplomatico-firenze') {
                $this->saveDiplomaticoMetadata();
            }
        }
        return $input;
    }

    private function readEad()
    {
        $res = [];
        $this->completeDom = new DOMDocument();
        $content = str_replace("xmlns=\"\"", '', file_get_contents($this->filename));
        $this->completeDom->loadXML(str_replace("xmlns=", "xmlns:faked=", $content));
        $this->xpathComplete = new DOMXPath($this->completeDom);
        //$dom->loadXML(str_replace("xmlns=", "xmlns:faked=", file_get_contents($this->filename)));
        $domRoots = $this->completeDom->getElementsByTagName('ead');
        foreach ($domRoots as $d) {
            $unitid = $d->getElementsByTagName('unitid');
            if ($unitid->length) {
                $domRoot[] = $d;
            }
        }
        foreach ($domRoot as $doc) {
            $recordBody = new DOMDocument();
            $docString = $doc->ownerDocument->saveXML($doc);
            $recordBody->loadXML(str_replace('ead:', '', $docString));
            $res[] = $recordBody;
        }
        return $res;
    }

    private function mapHref()
    {
        $daoSingleNode = $this->xpath->query($this->daoPaths->daoSingle->xpath);
        foreach ($daoSingleNode as $node) {
            $otherType = $node->getAttribute('otherdaotype');
            if ($otherType == 'METS') {
                $this->mapHrefDaoMets($node);
            } elseif ($otherType == 'MAG') {
                $this->mapHrefDaoMags($node);
            } else {
                $this->mapHrefDao($node);
            }
        }
    }

    private function mapHrefDao($node)
    {
        $href = ltrim($node->getAttribute('href'), '/');
        $title = $node->getAttribute('label') ?: $node->getAttribute('identifier');
        $parentNode = $node->parentNode;
        $id = $this->xpath->query($this->daoPaths->id->xpath, $parentNode)[0]->nodeValue;
        $daoset = $this->xpath->query('../../daoset', $node);
        if($daoset->length == 0) {
            $levelNode = $this->xpath->query('./@level', $parentNode->parentNode);
        } else {
            $levelNode = $this->xpath->query('../@level', $parentNode->parentNode);
        }
        $this->tableModel[$id] = $levelNode[0]->nodeValue;
        $this->tableDao[$id][] = $href;
        $this->tableTitle[$href] = $title;
    }

    private function mapHrefDaoMets($node)
    {
        $href = $node->getAttribute('href');
        $parentNode = $node->parentNode;
        $id = $this->xpath->query($this->daoPaths->id->xpath, $parentNode)[0]->nodeValue;
        $this->tableModel[$id] = $this->xpath->query($this->daoPaths->levelMets->xpath, $parentNode->parentNode)[0]->nodeValue;
        if (strpos($href, 'file:') !== 0) {
            $amdNode = $this->xpathComplete->query("//mets:amdSec[@ID='AMD_$id']");
            if (!$amdNode->length) {
                $id2 = str_replace(' ', '_', $id);
                $amdNode = $this->xpathComplete->query("//mets:amdSec[@ID='AMD_$id2']");
            }
        } else {
            $amdNode = $this->loadMetsNode($href, 'amdSec', true);
        }
        if ($amdNode->length) {
            $metsNode = $amdNode[0]->parentNode;
            $flocat = $metsNode->getElementsByTagName('FLocat');
            foreach ($flocat as $location) {
                $href = $location->getAttribute('xlink:href');
                $this->tableMets[$id][] = $href;
                $idImg = $location->parentNode->getAttribute('ID');
                if (!$this->metsXpath) {
                    $fptrTitle = $this->xpathComplete->query("//mets:fptr[@FILEID='$idImg']/..");
                } else {
                    $fptrTitle = $this->metsXpath->query("//mets:fptr[@FILEID='$idImg']/..");
                }
                $this->tableTitle[$href] = $fptrTitle[0]->getAttribute('LABEL');
            }
        }
    }

    private function loadMetsNode($href, $nodeName, $createXpath)
    {
        $path = dirname($this->filename) . '/img/' . str_replace('file:', '', $href);
        $metsDOM = new DOMDocument();
        if (file_exists($path)) {
            $metsDOM->load($path);
            if ($createXpath) {
                $this->metsXpath = new DOMXPath($metsDOM);
                $this->metsXpath->registerNamespace('mets', 'http://www.loc.gov/METS/');
            }
            $node = $metsDOM->getElementsByTagName($nodeName);
            if ($node->length) {
                return $node;
            }
        }
        return null;
    }

    private function importImg($table)
    {
        $media = new StdClass();
        foreach ($table as $key => $paths) {
            $imageForContainer['addMedias'] = array();
            $imgSequence = [];
            $containarName = $key;
            foreach ($paths as $fn) {
                $fp = $this->importFolder . "/img/$fn";
                if (file_exists($fp)) {
                    $info = pathinfo($fp, PATHINFO_BASENAME);
                    $importPath = $this->uploadFolder . "/$info";
                    //$fp = escapeshellarg($fp);
                    //$importPathEsc = escapeshellarg($importPath);
                    //exec("cp $fp $importPathEsc");
                    $mediaExists = $this->dam->mediaExists($fp);
                    $media->title = $this->tableTitle[$fn];
                    $media->filename = $fp;
                    $mediaData = array();
                    $mediaData['addMedias'][] = array(
                        'MainData' => $media,
                        'bytestream' => realpath($fp)
                    );
                    $mediaData = json_encode($mediaData);
                    if (!$mediaExists['response']) {
                        $res = $this->dam->insertMedia($mediaData);
                        $idRes = $res->ids[0];
                    } else {
                        $idRes = $mediaExists['ids'][0];
                    }
                    if ($idRes) {
                        $json = $this->dam->getJSON($idRes, $media->title);
                        if (count($paths) < 2) {
                            $this->linkSingleToRecord($key, $json);
                        } else {
                            $result = json_decode($json);
                            $this->addToContainer($result, $imageForContainer);
                            $this->addToFileSequence($result, $media->title, $fp, $imgSequence);
                        }
                    } else {
                        $this->fileLogger->writeLogLine('WARNING', $fn, "C'è stato un errore nell'importazione dell'immagine");
                    }
                } else {
                    $this->fileLogger->writeLogLine('WARNING', $fn, "L'immagine non è stata individuata");
                }
            }
            $this->cleanImportFolder();
            if (count($paths) > 1 && count($imageForContainer['addMedias'])) {
                $this->dam->addMediaToContainer($containarName, json_encode($imageForContainer), $imageForContainer['addMedias'][0]);
                $metadata = $this->createMetadata($imgSequence, $containarName);
                $struMag = new StdClass();
                $struMag->id = $metadata[0];
                $struMag->text = $metadata[1];
                $this->linkedMetadataToRecord($key, $struMag);
            }
        }
        //$this->cleanImportFolder(true);
    }

    private function buildImportFolderPath()
    {
        $path = dirname($this->filename);
        $elements = explode('/', $path);
        return __Config::get('metafad.importer.uploadFolder') . '/' . end($elements);
    }

    private function createUploadFolder()
    {
        $upDir = __Config::get('metafad.dam.upload.folder');
        if (!file_exists($upDir . 'icar-import_img')) {
            mkdir($upDir . 'icar-import_img');
        }
        return $upDir . 'icar-import_img';
    }

    private function linkSingleToRecord($id, $json)
    {
        $model = $this->detectModel($id);
        $it = pinax_ObjectFactory::createModelIterator($model)->where('externalID', $id);
        if ($it->count()) {
            $ar = $it->first();
            $data = $ar->getRawData();
            $data->mediaCollegati = $json;
            if ($this->instituteKey == 'diplomatico-firenze' && $model == 'archivi.models.UnitaDocumentaria') {
                $this->tableIdentifier[] = $ar->getId();
            }
            $this->save($data, $ar);
        }
    }

    private function linkedMetadataToRecord($id, $metadata)
    {
        $model = $this->detectModel($id);
        $it = pinax_ObjectFactory::createModelIterator($model)->where('externalID', $id);
        if ($it->count()) {
            $ar = $it->first();
            $data = $ar->getRawData();
            $data->linkedStruMag = $metadata;
            if ($this->instituteKey == 'diplomatico-firenze' && $model == 'archivi.models.UnitaDocumentaria') {
                $this->tableIdentifier[] = $ar->getId();
            }
            $this->save($data, $ar);
        }
    }

    private function save($data, $ar)
    {
        $data->__model = $ar->document_type;
        $data->__id = $ar->document_id;
        $this->arcProxy->save($data);
        $ar->deleteStatus('OLD');
    }

    private function addToContainer($data, &$container)
    {
        $container['addMedias'][] = $data->id;
    }

    private function addToFileSequence($data, $info, $importPath, &$imgSequence)
    {
        $data->title = $info;
        $data->label = $info;
        $data->type = 'IMAGE';
        $data->file_name = $importPath;
        $imgSequence[] = $data;
    }

    private function cleanImportFolder($root = false)
    {
        // $folder = "./icar-import_img/*";
        // if ($root) {
        //     $folder = '/*';
        // }
        $directories = glob(__Config::get('metafad.dam.upload.folder'), GLOB_ONLYDIR);
        foreach ($directories as $dir) {
            pinax_helpers_Files::deleteDirectory($dir);
        }
    }

    function createMetadata($images, $struName)
    {
        $logicalSTRU[] = array('folder' => false, 'key' => 'exclude', 'title' => 'Elementi esclusi');
        $physicalSTRU = array('image' => array());
        $strumag = pinax_ObjectFactory::createModel('metafad.strumag.models.Model');
        $strumag->title = 'meta_' . $struName;
        $strumag->state = 0;

        foreach ($images as $index => $image) {
            $image->keyNode = null;
            $physicalSTRU['image'][] = $image;
        }

        $strumag->physicalSTRU = json_encode($physicalSTRU);
        $strumag->logicalSTRU = json_encode($logicalSTRU);

        $id = $strumag->publish();

        $decodeData = (object) $strumag->getValuesAsArray();

        $cl = new stdClass();

        $it = pinax_ObjectFactory::createModelIterator('metafad.strumag.models.Model');

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
        $decodeData->__id = $id;
        $decodeData->__model = 'metafad.strumag.models.Model';

        $decodeData->document = json_encode($cl);
        $decodeData->__commit = true;
        $this->event->insert($decodeData);
        return [$id, $strumag->title];
    }

    private function mapHrefDaoMags($node)
    {
        $href = $node->getAttribute('href');
        $parentNode = $node->parentNode;
        $id = $this->xpath->query($this->daoPaths->id->xpath, $parentNode)[0]->nodeValue;
        $this->tableModel[$id] = $this->xpath->query($this->daoPaths->level->xpath, $parentNode->parentNode)[0]->nodeValue;
        $this->tableMagsFile[$id][] = $href;
    }

    private function importMag($table)
    {
        foreach ($table as $key => $file) {
            $path = dirname($this->filename) . '/img/' . str_replace('file:', '', $file[0]);
            if (!file_exists($path)) {
                $this->fileLogger->writeLogLine('WARNING', $path, "Il file MAG non è stato individuato");
                continue;
            }
            $importService = pinax_ObjectFactory::createObject(
                'metafad.mag.services.ImportMag',
                $this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy'),
                $this->dam,
                $this->event
            );
            $res = $importService->importFolder([$path], ['M', 'S'], false, false, $this->overwrite);
            if ($res === 'XMLError') {
                $this->fileLogger->writeLogLine('WARNING', $path, "Il file MAG presenta problemi di sintassi");
                continue;
            }
            $importService->linkMedia($this->detectModel($key), $key);
        }
    }

    private function detectModel($id)
    {
        if ($this->tableModel[$id] !== 'item') {
            return 'archivi.models.UnitaArchivistica';
        }
        return 'archivi.models.UnitaDocumentaria';
    }

    function saveDiplomaticoMetadata()
    {
        $archiviProxy = pinax_ObjectFactory::createObject("archivi_models_proxy_ArchiviProxy");
        $archiviProxy->setRetryWithDraftOnInvalidate(true);
        $archiviProxy->setUpdateConsProdBidirectional(false);
        foreach ($this->tableIdentifier as $id) {
            $ar = pinax_ObjectFactory::createModel('archivi.models.UnitaDocumentaria');
            $ar->load($id);
            $doc = $ar->documentazioneArchivioCollegata[0];
            if (!$doc || !$doc->documentazione_url || !$doc->localizzazioneSegnatura) {
                continue;
            }
            $externalID = $doc->localizzazioneSegnatura;
            $imageTarget = $doc->documentazione_url;
            $it = pinax_ObjectFactory::createModelIterator('archivi.models.UnitaArchivistica')->where('externalID', $externalID);
            if (!$it->count()) {
                throw new Exception("Scheda unita (tomo) $externalID non importata");
            }
            $tomo = $it->first();
            $struMagLink = $tomo->linkedStruMag;
            $strumag = pinax_ObjectFactory::createModel('metafad.strumag.models.Model');
            $strumag->load($struMagLink['id']);
            $physicalSTRU = json_decode($strumag->physicalSTRU);
            $images = $physicalSTRU->image;
            foreach ($images as $img) {
                if ($img->label == $imageTarget) {
                    $uuid = $img->id;
                    break;
                }
            }
            $secondaryStruMag = new stdClass();
            $secondaryStruMag->relatedStruMag = $struMagLink;
            $uuidImage = new stdClass();
            $uuidImage->id = $img->id;
            $uuidImage->text = $img->label;
            $secondaryStruMag->uuidImageSecondary = $uuidImage;
            $arr = [$secondaryStruMag];
            $data = $ar->getRawData();
            $data->__id = $ar->document_id;
            $data->__model = 'archivi.models.UnitaDocumentaria';
            $data->secondaryStruMag = $arr;
            $data->documentazioneArchivioCollegata[0]->documentazione_url = $tomo->getId();
            $archiviProxy->save($data);
            $ar->deleteStatus('OLD');
        }
    }

    function validateInput($input)
    {
    }
}
