<?php

class metafad_common_importer_operations_StrumentiPresaveCorrections extends metafad_common_importer_operations_LinkedToRunner
{
    function __construct($params, $runner)
    {
        parent::__construct($params, $runner);
    }

    private function dataApertaToDataSingola($data)
    {
        if (!count($data->cronologiaRedazione)) {
            return;
        }

        foreach ($data->cronologiaRedazione as $cronologia) {
            if ($cronologia->tipoData == 'data-aperta') {
                $cronologia->estremoRecente_data = $cronologia->estremoRemoto_data;
                $cronologia->estremoRecente_codificaData = $cronologia->estremoRemoto_codificaData;
                $cronologia->tipoData = 'data-singola';
            }
        }
    }

    protected function implodeOsservazioni($data)
    {
        $fieldValue = '';
        $osservazioni = $data->osservazioni;
        foreach ($osservazioni as $o) {
            foreach ($o as $value) {
                $fieldValue .= " $value";
            }
        }
        $data->osservazioni = ltrim($fieldValue, ' ');
    }

    protected function corrections($data)
    {
        $this->dataApertaToDataSingola($data);
        $this->implodeOsservazioni($data);

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
