<?php

class metafad_common_importer_functions_solvers_TipologiaSedeSolver implements metafad_common_importer_functions_solvers_SolverInterface
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
        foreach ($array as $ob) {
            if ($ob->principale == 'S') {
                $ret[] = 'Sede principale';
            } elseif ($ob->consultazione == 'S') {
                $ret[] = 'Sede per consultazione';
            } elseif (strpos($ob->denominazione, 'didattica') !== false) {
                $ret[] = 'Sede per la didattica';
            } elseif (strpos($ob->denominazione, 'sussidiaria') !== false) {
                $ret[] = 'Sede sussidiaria';
            } else {
                $ret[] = '';
            }
        }
        return $ret;
    }
}
