<?php

class metafad_common_importer_functions_solvers_ArraysToString implements metafad_common_importer_functions_solvers_SolverInterface
{
    private $separator = "; ";

    /**
     * Si aspetta un params stdClass con:
     * separator = stringa che indica se il separatore degli items
     * @param $params
     */
    function __construct($params){
        $this->separator = $params->separator ?: $this->separator;
    }

    /**
     * Restituisce un array con una singola stringa
     * @param array $array
     * @return array
     */
    function solveConflict($array)
    {
        $ret[] = implode($this->separator, $array);
        return $ret;
    }
}