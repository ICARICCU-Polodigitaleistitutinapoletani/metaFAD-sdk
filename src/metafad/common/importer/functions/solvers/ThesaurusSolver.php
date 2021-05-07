<?php

class metafad_common_importer_functions_solvers_ThesaurusSolver implements metafad_common_importer_functions_solvers_SolverInterface
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
        foreach ($array as $val) {
            $dictionary = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusDetails')
                ->where('thesaurus_code', 'VC_Localizzazione')
                ->where('thesaurusdetails_value', $val);
            foreach ($dictionary as $ar) {
                $ret[] = $ar->thesaurusdetails_key;
            }
        }
        return $ret;
    }
}
