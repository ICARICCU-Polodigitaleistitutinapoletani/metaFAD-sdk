<?php

class metafad_exporter_services_ead3exporter_ProcessorArchiveLevel extends metafad_exporter_services_ead3exporter_BaseProcessor
{
    //TODO mantenere fisso l'ordine delle schede annidate (salvando una scheda, attualmente l'ordine cambia)
    private $rootMapping;
    private $CAMapping;
    private $UAMapping;
    private $UDMapping;
    private $models;
    private $idsForMets;
    protected $idsImages;
    private $dirRead;
    protected $metsExporter;

    public function __construct($dirRead, $docWrite, $domConfig, $instituteKey)
    {
        parent::__construct($docWrite, $domConfig, $instituteKey);
        $this->dirRead = $dirRead;
        $this->rootMapping = json_decode(file_get_contents($dirRead . "maps/root.json"));
        $this->CAMapping = json_decode(file_get_contents($dirRead . "maps/EAD3Export_CA.json"));
        $this->UAMapping = json_decode(file_get_contents($dirRead . "maps/EAD3Export_UA.json"));
        $this->UDMapping = json_decode(file_get_contents($dirRead . "maps/EAD3Export_UD.json"));
        $this->defaultMapping = json_decode(file_get_contents($dirRead . "maps/default.json"));
        $this->models = ['archivi.models.ComplessoArchivistico', 'archivi.models.UnitaArchivistica', 'archivi.models.UnitaDocumentaria'];
        $this->defineBaseXpath('/ead:ead/ead:archdesc');
        $this->defineNamespace('ead', 'http://ead3.archivists.org/schema/');
        $this->defineConfigDOMElement('ead3');
        $this->generateMetadataBlocks();
        $this->idsForMets = [];
        $this->idsImages = [];
    }

    public function processRoots($id)
    {
        $this->context = null;
        $this->processDefault();
        $entity = pinax_ObjectFactory::createModel('archivi.models.ComplessoArchivistico');
        if ($entity->load($id)) {
            foreach ($this->rootMapping as $field => $properties) {
                $field = str_replace('#', '', $field);
                if (!is_object($properties)) {
                    $this->processField($field, $properties, $entity);
                } else {
                    $this->processObjectField($field, $properties, $entity);
                }
            }
        }
        $this->findChildren($id, $entity->identificativo);
        $preSave = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_PreSaveCorrectionsArchiveLevel", $this->docWrite);
        $this->docWrite = $preSave->doCorrections();
        return $this->docWrite;
    }

    private function processLevel($entity, $parentMetaID, $model)
    {
        $this->context = $this->buildCNode($parentMetaID, $entity->identificativo);
        $mapping = $this->detectMapping($model);
        foreach ($this->$mapping as $field => $properties) {
            $field = str_replace('#', '', $field);
            if (!is_object($properties)) {
                $this->processField($field, $properties, $entity);
            } else {
                $this->processObjectField($field, $properties, $entity);
            }
        }
        $this->findChildren($entity->getId(), $entity->identificativo);
    }

    private function buildCNode($metaID, $domID)
    {
        $parentNode = $this->xpath->query("//ead:c[./ead:did/ead:unitid/text()='$metaID']");
        if ($parentNode->length != 1) {
            $parentNode = $this->xpath->query("/ead:ead/ead:archdesc[./ead:did/ead:unitid/text()='$metaID']");
            $dscElement = $this->docWrite->createElement('dsc');
            $parentNode[0]->appendChild($dscElement);
            $this->updateDOM();
            $parentNode = $this->xpath->query("/ead:ead/ead:archdesc[./ead:did/ead:unitid/text()='$metaID']/ead:dsc");
        }
        $CElement = $this->docWrite->createElement('c');
        $cNode = $parentNode[0]->appendChild($CElement);
        $didElement = $this->docWrite->createElement('did');
        $didNode = $cNode->appendChild($didElement);
        $unitidElement = $this->docWrite->createElement('unitid', $domID);
        $unitidLocType = $this->docWrite->createAttribute('localtype');
        $unitidIdentifier = $this->docWrite->createAttribute('identifier');
        $unitidLocType->value = 'metaFAD';
        $unitidIdentifier->value = $domID;
        $unitidElement->appendChild($unitidLocType);
        $unitidElement->appendChild($unitidIdentifier);

        $didNode->appendChild($unitidElement);
        $this->updateDOM();
        return $this->xpath->query("//ead:c[./ead:did/ead:unitid/text()='$domID']")[0];
    }

    private function findChildren($id, $metaID)
    {
        foreach ($this->models as $model) {
            $children = pinax_ObjectFactory::createModelIterator($model)->where('parent', $id);
            foreach ($children as $child) {
                if ($child->document_type != 'archivi.models.ComplessoArchivistico') {
                    $linkedStruMag = $child->linkedStruMag;
                    if (is_array($linkedStruMag) && is_numeric($linkedStruMag['id'])) {
                        $this->idsForMets[] = $child->getId();
                        $this->idsImages[str_replace(' ', '_', $child->identificativo)] = $linkedStruMag['id'];
                    } elseif($child->mediaCollegati) {
                        $this->idsForMets[] = $child->getId();
                        $this->idsImages[str_replace(' ', '_', $child->identificativo)] = $child->mediaCollegati;
                    }
                }
                $this->processLevel($child, $metaID, $model);
            }
        }
    }

    private function detectMapping($model)
    {
        switch ($model) {
            case 'archivi.models.ComplessoArchivistico':
                return 'CAMapping';
            case 'archivi.models.UnitaArchivistica':
                return 'UAMapping';
            case 'archivi.models.UnitaDocumentaria':
                return 'UDMapping';
        }
    }

    public function addDaoSegments()
    {
        $DAOsegment = json_decode(file_get_contents($this->dirRead . "maps/DAOsegments.json"));
        foreach ($DAOsegment as $key => $segment) {
            $this->UAMapping->$key = $segment;
            $this->UDMapping->$key = $segment;
        }
    }

    public function getIdsForMets()
    {
        return $this->idsForMets;
    }

    public function getIdsImages()
    {
        return $this->idsImages;
    }

    public function setMetsExporter($bool)
    {
        $this->metsExporter = $bool;
    }
}
