<?php

class metafad_exporter_services_ead3exporter_ProcessorStrumentiDiRicerca extends metafad_exporter_services_ead3exporter_BaseProcessor
{
    private $strumentiMapping;

    public function __construct($dirRead, $docWrite, $domConfig, $instituteKey)
    {
        parent::__construct($docWrite, $domConfig, $instituteKey);
        $this->strumentiMapping = json_decode(file_get_contents($dirRead . "maps/EAD3Export_Strumenti.json"));
        $this->defaultMapping = json_decode(file_get_contents($dirRead . "maps/defaultStrumento.json"));
        $this->defineBaseXpath('/ead:ead');
        $this->defineNamespace('ead', 'http://ead3.archivists.org/schema/');
        $this->defineConfigDOMElement('ead3_strumenti');
    }

    // public function defineBaseXpath($baseXpath)
    // {
    //     $this->baseXpath = $baseXpath;
    // }

    // public function defineNamespace($prefix, $uri) {
    //     $this->namespacePrefix = $prefix;
    //     $this->namespaceURI = $uri;
    //     $this->xpath->registerNamespace($prefix, $uri);
    // }

    // public function defineConfigDOMElement($element) {
    //     $this->configDOMElement = $element;
    // }

    public function processRoots($id)
    {
        $this->context = null;
        $this->processDefault();
        $entity = pinax_ObjectFactory::createModel('archivi.models.SchedaStrumentoRicerca');
        if ($entity->load($id)) {
            foreach ($this->strumentiMapping as $field => $properties) {
                $field = str_replace('#', '', $field);
                if (!is_object($properties)) {
                    $this->processField($field, $properties, $entity);
                } else {
                    $this->processObjectField($field, $properties, $entity);
                }
            }
        }
        return $this->docWrite;
    }
}
