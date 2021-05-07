<?php

class metafad_exporter_services_ead3exporter_ProcessorProduttori extends metafad_exporter_services_ead3exporter_BaseProcessor
{
    private $prodMapping;

    public function __construct($dirRead, $docWrite, $domConfig, $instituteKey)
    {
        parent::__construct($docWrite, $domConfig, $instituteKey);
        $this->prodMapping = json_decode(file_get_contents($dirRead . "maps/EAC-CPF_Produttori.json"));
        $this->defaultMapping = json_decode(file_get_contents($dirRead . "maps/defaultProduttore.json"));
        $this->defineBaseXpath('/eac-cpf:eac-cpf');
        $this->defineNamespace('eac-cpf', 'urn:isbn:1-931666-33-4');
        $this->defineConfigDOMElement('eac-cpf');
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

    public function processRoots($id)
    {
        $this->context = null;
        $this->processDefault();
        $entity = pinax_ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
        if ($entity->load($id)) {
            foreach ($this->prodMapping as $field => $properties) {
                $field = str_replace('#', '', $field);
                if (!is_object($properties)) {
                    $this->processField($field, $properties, $entity);
                } else {
                    $this->processObjectField($field, $properties, $entity);
                }
            }
        }
        $preSave = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_PreSaveCorrectionsProduttori", $this->docWrite);
        $this->docWrite = $preSave->doCorrections();
        return $this->docWrite;
    }
}
