<?php

/**
 *
 */
class metafad_common_importer_functions_transformers_AntroponimoFromStdClass implements metafad_common_importer_functions_transformers_TransformerInterface
{
    /**
     * metafad_common_importer_functions_transformers_AntroponimoFromStdClass constructor.
     * @param $params
     */
    private $buildIntestazione;
    public function __construct($params)
    {
        $this->buildIntestazione = $params->buildIntestazione;
    }

    /**
     * @param $stdClass
     * @return mixed
     * @throws Exception
     */
    private function transformSingle($stdClass)
    {
        if (!is_object($stdClass)) {
            throw new Exception("Chiamata AntoponimoFromStdClass su una variabile che non contiene una stdClass");
        }

        $copy = clone $stdClass;

        if ($this->buildIntestazione) {
            $stdClass->intestazione = $this->buildIntestazione($stdClass);
        }

        $data = metafad_common_helpers_ImporterCommons::inferAntroponimoFromIntestazione($stdClass->intestazione);

        foreach ($data as $k => $v) {
            $stdClass->$k = $v;
        }

        //Ripopola i valori della voce d'indice con quelli orginiari a inferAntroponimoFromIntestazione
        foreach ($copy as $k => $v) {
            if ($k != 'intestazione')
                $stdClass->$k = $copy->$k;
        }

        /**
         * @var $proxy archivi_models_proxy_ArchiviProxy
         */
        $proxy = pinax_ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
        $proxy->isImportProcess();
        $ret = $proxy->addOrGetMiniAuthorityLink($stdClass, "archivi.models.Antroponimi", 'externalID', 'intestazione');
        unset($proxy);
        return (object)$ret;
    }

    public function transformItems($array)
    {
        $ret = array();
        $array = array_filter($array, function ($a) {
            return $a;
        });
        foreach ($array as $item) {
            $ret[] = $this->transformSingle($item);
        }
        return $ret;
    }

    //Concatena solo la qualifica. Se dovessero servire altri campi, bisogna integrare
    private function buildIntestazione($stdClass)
    {
        $intestazione = $stdClass->intestazione;
        if ($stdClass->qualificazione) {
            $intestazione .= ' <' . $stdClass->qualificazione . '>';
        }
        return $intestazione;
    }
}
