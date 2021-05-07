<?php
set_time_limit(0);
ini_set('memory_limit', '2048M');

class metafad_exporter_services_ead3exporter_RecordsExporter extends PinaxObject
{
    private $dirRead;
    private $docWrite;
    private $processor;
    private $idsForMets;
    private $idsImages;
    private $metsExporter;
    private $exporterType;

    public function __construct()
    {
        $this->dirRead = pinax_findClassPath("metafad/exporter/services/ead3exporter/input", false) . "/";
        $this->docWrite = new DOMDocument();
        $this->idsForMets = [];
    }

    public function processRecord($processorType, $templateType, $domConfig, $instKey, $id, $counter)
    {
        $document = $this->createImportRecordContainer($templateType, $counter);
        $this->docWrite->load($this->dirRead . $templateType);
        $this->processor = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_$processorType", $this->dirRead, $this->docWrite, $domConfig, $instKey);
        if($processorType == 'ProcessorArchiveLevel' && $this->metsExporter == 'dao') {
            $this->processor->addDaoSegments();
        }
        if($processorType == 'ProcessorMETS') {
            $this->processor->setOnlyHref($this->exporterType);
        }
        $this->docWrite = $this->processor->processRoots($id, $this->docWrite);
        if($processorType == 'ProcessorArchiveLevel') {
            $this->idsForMets = $this->processor->getIdsForMets();
            $this->idsImages = $this->processor->getIdsImages();
        }
        $this->docWrite->saveXML();
        $this->appendDocument($document);
        return $document;
    }

    private function createImportRecordContainer($type, $counter)
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = true;
        $document->formatOutput = true;
        $document->load($this->dirRead . 'recordHeaderTemplate.xml');
        $node = $document->getElementsByTagName('icar-import:recordHeader');
        $schedaType = '';
        switch ($type) {
            case 'ead3.xml':
                $node[0]->setAttribute('type', 'ead3');
                $node[0]->setAttribute('groupEad', 'multiple');
                $schedaType = 'ca';
                break;
            case 'scons2.xml':
                $node[0]->setAttribute('type', 'scons');
                $schedaType = 'sc';
                break;
            case 'eac-cpf.xml':
                $node[0]->setAttribute('type', 'eac-cpf');
                $schedaType = 'sp';
                break;
            case 'ead3Strumenti.xml':
                $node[0]->setAttribute('type', 'ead3');
                $node[0]->setAttribute('groupEad', 'single');
                $schedaType = 'sr';
                break;
            case 'mets.xml':
                $node[0]->setAttribute('type', 'mets');
                $schedaType = 'me';
                break;
        }
        $idNode = $document->getElementsByTagName('icar-import:id');
        $dateNode = $document->getElementsByTagName('icar-import:lastUpdate');
        $date = date('c');
        if ($pos = strpos($date, '+')) {
            $date = substr($date, 0, $pos);
        }
        $dateNode[0]->nodeValue = $date;
        $idNode[0]->nodeValue = "metafad:$schedaType-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
        $document->saveXML();
        return $document;
    }

    private function appendDocument($document)
    {
        $element = $document->getElementsByTagName('icar-import:recordBody');
        $nodeToImport = $this->docWrite->documentElement;
        $nodeToImport = $document->importNode($nodeToImport, true);
        $element[0]->appendChild($nodeToImport);
        $document->saveXML();
    }

    public function checkIfUpdateRecordList()
    {
        $records = $this->processor->getRecordToProcess();
        if (count($records)) {
            return true;
        }
        return false;
    }
    public function getRecordToProcess()
    {
        return $this->processor->getRecordToProcess();
    }

    public function getIdsForMets() {
        return $this->idsForMets;
    }

    public function getIdsImages() {
        return $this->idsImages;
    }

    public function setMetsExporter($bool) {
        $this->metsExporter = $bool;
    }

    public function setExportType($type) {
        $this->exporterType = $type;
    }
}
