<?php

/**
 *
 */
class metafad_common_importer_functions_transformers_ToponimoFromStdClass implements metafad_common_importer_functions_transformers_TransformerInterface
{
    /**
     * metafad_common_importer_functions_transformers_ToponimoFromStdClass constructor.
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
            throw new Exception("Chiamata ToponimoFromStdClass su una variabile che non contiene una stdClass");
        }

        $copy = clone $stdClass;

        if ($this->buildIntestazione) {
            $stdClass->intestazione = $this->buildIntestazione($stdClass);
        }
        $data = metafad_common_helpers_ImporterCommons::inferToponimoFromIntestazione($stdClass->intestazione);

        foreach ($data as $k => $v) {
            $stdClass->$k = $v;
        }

        //Ripopola i valori della voce d'indice con quelli orginiari a inferToponimoFromIntestazione
        foreach ($copy as $k => $v) {
            if ($k != 'intestazione')
                $stdClass->$k = $copy->$k;
        }

        /**
         * @var $proxy archivi_models_proxy_ArchiviProxy
         */
        $proxy = pinax_ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
        $proxy->isImportProcess();
        $ret = $proxy->addOrGetMiniAuthorityLink($stdClass, "archivi.models.Toponimi", 'externalID', 'intestazione');
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

    private function buildIntestazione($stdClass)
    {
        $count = 0;
        $intestazione = $stdClass->intestazione . ' <' . $stdClass->intestazione . '>';
        foreach ($stdClass as $k => $v) {
            ++$count;
            if ($count == 1 || is_null($v)) {
                continue;
            }
            $intestazione = substr_replace($intestazione, " ; $v>", strpos($intestazione, '>'));
        }
        return $intestazione;
    }
}
