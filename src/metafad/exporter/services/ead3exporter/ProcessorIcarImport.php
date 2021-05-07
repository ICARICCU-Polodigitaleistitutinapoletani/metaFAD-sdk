<?php

class metafad_exporter_services_ead3exporter_ProcessorIcarImport extends metafad_exporter_services_ead3exporter_BaseProcessor
{

    public function __construct($dirRead, $docWrite, $domConfig, $instituteKey)
    {
        parent::__construct($docWrite, $domConfig, $instituteKey);
        $this->defaultMapping = json_decode(file_get_contents($dirRead . "maps/defaultIcarImport.json"));
        $this->defineBaseXpath('/icar-import:icar-import');
        $this->defineNamespace('icar-import', 'http://www.san.beniculturali.it/icar-import');
        $this->defineConfigDOMElement('icar_import');
    }

    public function defineBaseXpath($baseXpath)
    {
        $this->baseXpath = $baseXpath;
    }

    public function defineNamespace($prefix, $uri) {
        $this->namespacePrefix = $prefix;
        $this->namespaceURI = $uri;
        $this->xpath->registerNamespace($prefix, $uri);
    }

    public function defineConfigDOMElement($element) {
        $this->configDOMElement = $element;
    }

    public function processRoots($id)
    {
        $this->processDefault();
        return $this->docWrite;
    }
}
