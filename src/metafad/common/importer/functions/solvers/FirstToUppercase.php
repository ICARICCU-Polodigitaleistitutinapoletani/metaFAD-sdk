<?php

class metafad_common_importer_functions_solvers_FirstToUppercase implements metafad_common_importer_functions_solvers_SolverInterface
{
    /**
     * Si aspetta un params stdClass con:
     * @param $params
     */
    function __construct($params)
    {
    }

    function solveConflict($array)
    {
        foreach ($array as $str) {
            $ret[] = ucfirst($str);
        }
        return $ret;
    }
}
