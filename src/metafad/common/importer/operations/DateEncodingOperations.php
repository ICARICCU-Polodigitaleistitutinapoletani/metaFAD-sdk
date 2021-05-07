<?php
class metafad_common_importer_operations_DateEncodingOperations extends metafad_common_importer_operations_LinkedToRunner
{
    private $type;

    function __construct($params, $runner)
    {
        $this->type = $params->type;
        parent::__construct($params, $runner);
    }

    function execute($input)
    {
        $data = $input->data;
        $cronologia = $this->extractCronologia($data);
        if (!is_null($cronologia)) {
            $this->setEstremoCronologicoTestuale($cronologia);
            $this->setCodificaSecolare($cronologia);
            $this->setTipologiaData($cronologia);
        }
        return (object) array("data" => $data);
    }

    function setEstremoCronologicoTestuale($cronologia)
    {
        foreach ($cronologia as $c) {
            $c->estremoCronologicoTestuale = metafad_common_helpers_ImporterCommons::getCronologicoTestuale($c->estremoRemoto_data, $c->estremoRecente_data);
        }
    }

    function setTipologiaData($cronologia)
    {
        foreach ($cronologia as $c) {
            $c->tipoData = metafad_common_helpers_ImporterCommons::inferTipologiaData($c);
        }
    }

    function setCodificaSecolare($cronologia)
    {
        foreach ($cronologia as $c) {
            if (empty($c->estremoRemoto_codificaData)) {
                $c->estremoRemoto_codificaData = metafad_common_helpers_ImporterCommons::normalizeCentury(
                    $c->estremoRemoto_secolo,
                    $c->estremoRemoto_specifica
                );
            }
            if (empty($c->estremoRecente_codificaData)) {
                $c->estremoRecente_codificaData = metafad_common_helpers_ImporterCommons::normalizeCentury(
                    $c->estremoRecente_secolo,
                    $c->estremoRecente_specifica
                );
            }
        }
    }

    private function extractCronologia($data)
    {
        if (!$this->type) {
            return $data->cronologia;
        }

        $cronologiaArr = [];

        switch ($this->type) {
            case 'produttoreConservatore': {
                    $cronologiaArr[] = $data->cronologia[0];
                    if ($data->condizioneGiuridica) {
                        foreach ($data->condizioneGiuridica as $cg) {
                            $cronologiaArr[] = $cg->estremiCronologici[0];
                        }
                    }
                    if (is_array($data->ente_famiglia_denominazione) && count($data->ente_famiglia_denominazione)) {
                        foreach ($data->ente_famiglia_denominazione as $ob) {
                            $cronologiaArr[] = $ob->ente_famiglia_cronologia[0];
                        }
                        break;
                    }
                    if (is_array($data->famiglia_denominazione) && count($data->famiglia_denominazione)) {
                        foreach ($data->famiglia_denominazione as $ob) {
                            $cronologiaArr[] = $ob->famiglia_cronologia[0];
                        }
                        break;
                    }
                    if (is_array($data->persona_denominazione) && count($data->persona_denominazione)) {
                        foreach ($data->persona_denominazione as $ob) {
                            $cronologiaArr[] = $ob->persona_cronologia[0];
                        }
                        break;
                    }
                    if (is_array($data->soggettiConsConservatore) && count($data->soggettiConsConservatore)) {
                        foreach ($data->soggettiConsConservatore as $ob) {
                            $cronologiaArr[] = $ob->cronologiaConservatore[0];
                        }
                        break;
                    }
                }
            case 'strumento':
                if (is_array($data->cronologiaRedazione2)) {
                    $data->cronologiaRedazione = array_merge($data->cronologiaRedazione, $data->cronologiaRedazione2);
                    unset($data->cronologiaRedazione2);
                }
                foreach ($data->cronologiaRedazione as $cronologia) {
                    $cronologiaArr[] = $cronologia;
                }
                if (metafad_usersAndPermissions_Common::getInstituteKey() == 'diplomatico-firenze') {
                    $cronologiaArr[] = $this->cronologiaDiplomatico($data);
                }
        }
        $cronologiaArr =  $this->removeNull($cronologiaArr);
        return $cronologiaArr;
    }

    function removeNull($cronologiaArr)
    {
        return array_filter($cronologiaArr, function ($arr) {
            if ($arr) {
                return true;
            }
        });
    }

    function cronologiaDiplomatico($data)
    {
        $date = $data->cronologiaDiplomatico;
        if (!$date) {
            return null;
        }

        $cronologia = new StdClass();
        if (is_numeric($date)) {
            $cronologia->estremoRemoto_data = $date;
            $cronologia->estremoRecente_data = $date;
            $cronologia->estremoRemoto_codificaData = metafad_common_helpers_ImporterCommons::normalizeDate($date);
            $cronologia->estremoRecente_codificaData = metafad_common_helpers_ImporterCommons::normalizeDate($date);
        } elseif (strpos($date, 'sec.') !== false) {
            $cronologia->estremoRemoto_secolo = str_replace('sec. ', '', $date);
            $cronologia->estremoRecente_secolo = $cronologia->estremoRemoto_secolo;
            $cronologia->estremoRemoto_codificaData = metafad_common_helpers_ImporterCommons::normalizeCentury($cronologia->estremoRemoto_secolo);
            $cronologia->estremoRecente_codificaData = metafad_common_helpers_ImporterCommons::normalizeCentury($cronologia->estremoRecente_secolo);
        } elseif (strpos($date, 'post') !== false) {
            $cronologia->estremoRemoto_data = str_replace('post ', '', $date);
            $cronologia->estremoRecente_data = $cronologia->estremoRemoto_data;
            $cronologia->estremoRemoto_codificaData = metafad_common_helpers_ImporterCommons::normalizeDate($cronologia->estremoRemoto_data);
            $cronologia->estremoRecente_codificaData = metafad_common_helpers_ImporterCommons::normalizeDate($cronologia->estremoRecente_data);
            $cronologia->estremoRemoto_validita = 'Data post quem';
        } elseif (strpos($date, '(?)') !== false) {
            $cronologia->estremoRemoto_data = str_replace(' (?)', '', $date);
            $cronologia->estremoRecente_data = $cronologia->estremoRemoto_data;
            $cronologia->estremoRemoto_codificaData = metafad_common_helpers_ImporterCommons::normalizeDate($cronologia->estremoRemoto_data);
            $cronologia->estremoRecente_codificaData = metafad_common_helpers_ImporterCommons::normalizeDate($cronologia->estremoRecente_data);
            $cronologia->estremoRemoto_validita = 'Data incerta';
        } elseif (strpos($date, '-') !== false) {
            $arr = explode('-', $date);
            $cronologia->estremoRemoto_data = $arr[0];
            $cronologia->estremoRecente_data = $arr[1];
            $cronologia->estremoRemoto_codificaData = metafad_common_helpers_ImporterCommons::normalizeDate($arr[0]);
            $cronologia->estremoRecente_codificaData = metafad_common_helpers_ImporterCommons::normalizeDate($arr[1]);
        }
        $data->cronologiaRedazione[] = $cronologia;
        unset($data->cronologiaDiplomatico);
        return $cronologia;
    }

    function validateInput($input)
    {
    }
}
