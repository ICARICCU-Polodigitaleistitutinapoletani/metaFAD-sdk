<?php

/**
 * Aggiunge una label al campo (utile per i campi mappati nel campo Osservazioni)
 */
class metafad_common_importer_functions_transformers_AddLabel implements metafad_common_importer_functions_transformers_TransformerInterface
{
    private $label;

    /**
     * metafad_common_importer_functions_transformers_ExtractFromNormal constructor.
     * @param $params stdClass Si aspetta una stdClass con il campo "label" valorizzato
     */
    public function __construct($params)
    {
        @$this->label = $params->label;
    }

    /**
     * Aggiunge il valore nel dizionario se è "stringabile" e lo restituisce dopo aver aggiunto la label
     * @param $string
     * @throws
     * @return string
     */
    private function transformSingle($string){
        if (is_object($string) || is_array($string)){
            throw new Exception("Chiamata AddLabel su una variabile che non si converte in stringa");
        }
        $ret = $this->label . ": $string.";
        $ret = trim($ret, '.');
        $ret .= '.';

        return $ret;
    }

    public function transformItems($array)
    {
        $ret = array();

        foreach ($array as $item){
            $ret[] = $this->transformSingle($item);
        }

        return $ret;
    }
}