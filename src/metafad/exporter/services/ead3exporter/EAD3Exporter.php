<?php
set_time_limit(0);
ini_set('memory_limit', '2048M');

class metafad_exporter_services_ead3exporter_EAD3Exporter extends PinaxObject
{
    private $dirRead;
    private $dirWrite;
    private $docWriteIcarImport;
    private $fileName;
    private $domConfig;
    private $instKey;
    private $recordExporter;
    private $recordsToprocess;
    private $counter;
    private $ids;
    private $fileNameSingle;
    private $imagesExporter;
    private $exportFormat;
    private $exportType;
    private $jobId;
    private $processorIcarImport;

    public function __construct($id, $fileName, $filesConfig, $exportFormat, $exportType, $jobId)
    {
        $this->dirRead = pinax_findClassPath("metafad/exporter/services/ead3exporter/input", false) . "/";
        $this->dirWrite = "./export/ead3/";
        $this->instKey = metafad_usersAndPermissions_Common::getInstituteKey();
        $this->domConfig = new DOMDocument();
        $this->domConfig->load($filesConfig);
        $this->ids = $id;
        $this->exportFormat = $exportFormat;
        $this->exportType = $exportType;
        $this->recordExporter = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_RecordsExporter");
        $this->recordExporter->setMetsExporter($this->exportFormat);
        $this->recordExporter->setExportType($this->exportType);
        $this->fileName = $fileName;
        $this->createEad3Dir();
        $this->cleanExportFolder($this->dirWrite, $this->fileName);
        $this->jobId = $jobId;
    }

    public function export()
    {
        foreach ($this->ids as $id) {
            unset($this->recordsToprocess);
            $this->recordsToprocess[] = ['id' => $id, 'processorType' => 'ProcessorArchiveLevel', 'templateType' => 'ead3.xml'];
            $this->docWriteIcarImport = new DOMDocument();
            $this->docWriteIcarImport->load($this->dirRead . "icar-import.xml");
            $this->counter = 0;
            $this->fileNameSingle = $this->fileName . '_id_' . $id;
            $this->processorIcarImport = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_ProcessorIcarImport", $this->dirRead, $this->docWriteIcarImport, $this->domConfig, $this->instKey);
            $validationFailed = $this->processRecords();
            if ($validationFailed) {
                return true;
            }
        }
        $this->createZipArchive();
    }

    public function processRecords()
    {
        $this->docWriteIcarImport = $this->processorIcarImport->processRoots();
        while (count($this->recordsToprocess)) {
            ++$this->counter;
            $record = array_shift($this->recordsToprocess);
            $doc = $this->recordExporter->processRecord($record['processorType'], $record['templateType'], $this->domConfig, $this->instKey, $record['id'], $this->counter);
            if ($record['processorType'] == 'ProcessorArchiveLevel' && $this->exportType == 'file') {
                $idsImages = $this->recordExporter->getIdsImages();
                $this->imagesExporter = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_MediaExporter", $idsImages, $doc, $this->dirWrite, $this->exportFormat, $this->jobId);
                $doc = $this->imagesExporter->export();
            } elseif ($record['processorType'] == 'ProcessorArchiveLevel' && $this->exportType == 'link' && $this->exportFormat == 'mets') {
                $idsImages = $this->recordExporter->getIdsImages();
                $this->imagesExporter = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_MediaExporter", $idsImages, $doc, $this->dirWrite, $this->exportFormat, $this->jobId);
                $doc = $this->imagesExporter->metsLinker();
            }
            $this->appendToRecordList($doc);
            if (count($this->recordsToprocess) === 0) {
                $this->recordsToprocess = $this->recordExporter->getRecordToProcess();
            }
        }

        if ($this->exportFormat == 'mets') {
            $idsForMets = $this->recordExporter->getIdsForMets();
            foreach ($idsForMets as $id) {
                ++$this->counter;
                $doc = $this->recordExporter->processRecord('ProcessorMETS', 'mets.xml', $this->domConfig, $this->instKey, $id, $this->counter);
                $this->linkMetsDocuments($doc);
                $this->appendToRecordList($doc);
            }
        }

        //Blocco che serve per forzare la formattazione del documento
        $xml = $this->docWriteIcarImport->saveXML();
        $this->docWriteIcarImport->preserveWhiteSpace = false;
        $this->docWriteIcarImport->formatOutput = true;
        $this->docWriteIcarImport->loadXML($xml);
        // if ($this->validateDocument()) {
        //     return true;
        // }
        $this->docWriteIcarImport->save($this->dirWrite . $this->fileNameSingle . ".xml");
        return false;
    }

    private function appendToRecordList($document)
    {
        $element = $this->docWriteIcarImport->getElementsByTagName('listRecords');
        $nodeToImport = $document->documentElement;
        $nodeToImport = $this->docWriteIcarImport->importNode($nodeToImport, true);
        $element[0]->appendChild($nodeToImport);
        $this->docWriteIcarImport->saveXML();
    }

    private function createZipArchive()
    {
        $zip = new ZipArchive;
        if ($zip->open('./export/exportIcar/' . $this->fileName . '.zip', ZipArchive::CREATE) === TRUE) {
            $files = array_filter((array) glob($this->dirWrite . '*'));
            foreach ($files as $f) {
                if (is_dir($f)) {
                    $dir = pathinfo($f, PATHINFO_BASENAME);
                    $zip->addEmptyDir(pathinfo($f, PATHINFO_BASENAME));
                    $imgs = array_filter((array) glob($this->dirWrite . "$dir/*"));
                    foreach ($imgs as $img) {
                        $zip->addFile($img, "$dir/" . pathinfo($img, PATHINFO_BASENAME));
                    }
                } else {
                    $zip->addFile($f, pathinfo($f, PATHINFO_BASENAME));
                }
            }
            $zip->close();
        }
        $this->cleanExportFolder($this->dirWrite, $this->fileName);
    }

    private function cleanExportFolder()
    {
        array_map('unlink', array_filter((array) glob($this->dirWrite . '*')));
        $directories = glob($this->dirWrite . '/*', GLOB_ONLYDIR);
        foreach ($directories as $dir) {
            pinax_helpers_Files::deleteDirectory($dir);
        }
        array_map('unlink', array_filter((array) glob($this->dirWrite . '*')));
    }

    private function createEad3Dir()
    {
        if (!file_exists('./export/ead3')) {
            mkdir('./export/ead3');
        }

        if (!file_exists('./export/exportIcar')) {
            mkdir('./export/exportIcar');
        }
    }

    public function validateDocument()
    {
        $validator = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_XSDValidator");
        if ($validator->validate('http://www.san.beniculturali.it/tracciato/icar-import.xsd', $this->docWriteIcarImport, true, $this->dirWrite . 'validationReport.log', true, true)) {
            $this->createZipArchive();
            return true;
        }
    }

    public function linkMetsDocuments($doc)
    {
        $identifier = $doc->getElementsByTagName('identifier')[0]->nodeValue;
        $importId = $doc->getElementsByTagName('icar-import:id')[0]->nodeValue;
        $dao = $this->docWriteIcarImport->getElementsByTagName('ead:dao');
        foreach ($dao as $node) {
            $attr = $node->getAttribute('href');
            if ($attr == ($identifier . '.xml')) {
                $node->setAttribute('href', $importId);
                break;
            }
        }
    }
}
