<?php

class metafad_common_importer_functions_solvers_CompilationDateSolver implements metafad_common_importer_functions_solvers_SolverInterface
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
        foreach ($array as $date) {
            if (strpos($date, 'T')) {
                $date = substr($date, 0, strpos($date, 'T'));
            }
            $ret[] = str_replace('-', '/', $date);
        }
        return $ret;
    }
}
