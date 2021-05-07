<?php

class metafad_common_importer_operations_EACCPFPresaveCorrections extends metafad_common_importer_operations_LinkedToRunner
{
    function __construct($params, $runner)
    {
        parent::__construct($params, $runner);
    }

    private function fillTipologiaChoice($data) {
        if(!$data->tipologiaChoice) {
            $data->tipologiaChoice = 'Ente';
        }
    }

    private function addCronologiaToDenominazione($data)
    {
        if (!count($data->cronologia)) {
            return;
        }
        $arr = ['ente_famiglia_', 'famiglia_', 'persona_'];
        foreach ($arr as $v) {
            foreach ($data->{$v . 'denominazione'} as $block) {
                if (!$block->{$v . 'qualifica'} || !$block->{$v . 'qualifica'} == 'Denominazione principale') {
                    $block->{$v . 'cronologia'}[] = clone $data->cronologia[0];
                    break 2;
                }
            }
        }
        unset($data->cronologia);
    }

    protected function corrections($data)
    {
        $this->addCronologiaToDenominazione($data);
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
