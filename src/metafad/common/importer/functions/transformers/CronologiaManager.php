<?php

class metafad_common_importer_functions_transformers_CronologiaManager implements metafad_common_importer_functions_transformers_TransformerInterface
{
    private $type;
    private $splitter;

    public function __construct($params)
    {
        $this->type = $params->fieldType;
        $this->splitter = $params->splitter ? $params->splitter : '-';
    }

    private function transformSingle($string)
    {
        $strings = explode($this->splitter, $string);
        $strings[0] = ltrim($strings[0], '0');
        if ($strings[0] === '2099') {
            return '';
        }

        switch (strtoupper($this->type)) {
            case 'DATA_PUNTUALE':
                return metafad_common_helpers_ImporterCommons::formatDateYMD($strings[0], $strings[1], $strings[2]);
            case 'DATA_PUNTUALE_CODIFICA':
                return metafad_common_helpers_ImporterCommons::normalizeDate($strings[0], $strings[1], $strings[2]);
            case 'DATA_SECOLARE':
                return metafad_common_helpers_RomanService::extractRomanCentury($strings[0]);
            default:
                return '';
        }
    }

    public function inferSpecifica($array)
    {
        if (count($array) === 2) {
            $annoRemoto = explode($this->splitter, $array[0], 1)[0];
            $annoRecente = explode($this->splitter, $array[1], 1)[0];
            switch ($annoRecente - $annoRemoto) {
                case 9:
                    return $annoRemoto[2] == 4 ? 'Metà' : 'Inizio';
                case 10:
                    return 'Fine';
                case 24:
                    return ($annoRemoto[2] == 2 ? 'Secondo quarto' : $annoRemoto[2]) == 5 ? 'Terzo quarto' : 'Ultimo quarto';
                case 25:
                    return 'Primo quarto';
                case 49:
                    return 'Seconda metà';
                case 50:
                    return 'Prima metà';
                default:
                    return '';
            }
        } else {
            return '';
        }
    }

    public function transformItems($array)
    {
        $ret = array();
        if (strtoupper($this->type) === 'SECOLO_SPECIFICA') {
            $ret[] = $this->inferSpecifica($array);
            return $ret;
        }

        foreach (array_filter($array, function ($a) {
            return $a;
        }) as $item) {
            $ret[] = $this->transformSingle($item);
        }
        return $ret;
    }
}
