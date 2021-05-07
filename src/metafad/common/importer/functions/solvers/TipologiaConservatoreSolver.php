<?php

class metafad_common_importer_functions_solvers_TipologiaConservatoreSolver implements metafad_common_importer_functions_solvers_SolverInterface
{
    private $field;
    /**
     * Si aspetta un params stdClass con:
     * @param $params
     */
    function __construct($params)
    {
        $this->field = $params->field;
    }

    function solveConflict($array)
    {
        $ret = [];
        foreach ($array as $str) {
            $str = ucfirst($str);
            if ($this->field == 'tipologiaChoice') {
                if ($str != 'TesauroSAN/famiglia' && $str !== 'TesauroSAN/persona') {
                    $str = 'Ente';
                } elseif($str == 'TesauroSAN/famiglia') {
                    $str = 'Famiglia';
                } else {
                    $str = 'Persona';
                }
                $ret[] = $str;
            } elseif ($this->field == 'tipologiaEnte') {
                if ($str != 'Famiglia' && $str !== 'Persona' && $str != 'Ente') {
                    $ret[] = $str;
                }
            }
        }
        return $ret;
    }
}
