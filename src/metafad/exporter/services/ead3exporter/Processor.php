<?php

class metafad_exporter_services_ead3exporter_Processor extends metafad_exporter_services_ead3exporter_BaseProcessor
{
    private $consMapping;

    public function __construct($dirRead, $docWrite, $domConfig, $instituteKey)
    {
        parent::__construct($docWrite, $domConfig, $instituteKey);
        $this->consMapping = json_decode(file_get_contents($dirRead . "maps/SCONS2_Conservatori.json"));
        $this->defaultMapping = json_decode(file_get_contents($dirRead . "maps/defaultConservatore.json"));
        $this->defineBaseXpath('/scons:scons');
        $this->defineNamespace('scons', 'http://www.san.beniculturali.it/scons2');
        $this->defineConfigDOMElement('scons2');
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
        $entity = pinax_ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
        if ($entity->load($id)) {
            foreach ($this->consMapping as $field => $properties) {
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
