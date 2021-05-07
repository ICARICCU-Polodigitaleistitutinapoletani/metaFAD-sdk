<?php

class metafad_common_importer_operations_SCONSPresaveCorrections extends metafad_common_importer_operations_LinkedToRunner
{
    function __construct($params, $runner)
    {
        parent::__construct($params, $runner);
    }

    private function deleteDuplicateInObjects($data, $objects)
    {
        foreach ($objects as $ob => $subOb) {
            if (isset($data->$ob) && count($data->$ob) > 1) {
                for ($i = 1; $i < count($data->$ob); ++$i) {
                    $o = $data->$ob;
                    unset($o[$i]->$subOb);
                }
            }
        }
    }

    private function fillTipologiaChoice($data) {
        if(!$data->tipologiaChoice) {
            $data->tipologiaChoice = 'Ente';
        }
    } 

    protected function corrections($data)
    {
        $this->deleteDuplicateInObjects($data, ['sog_cons_sedi' => 'sog_cons_sedi_servizi']);
        $this->fillTipologiaChoice($data);
        return $data;
    }

    function execute($input)
    {
        $data = $this->corrections($input->data);

        return (object) array("data" => $data);
    }

    function validateInput($input)
    {
    }
}
