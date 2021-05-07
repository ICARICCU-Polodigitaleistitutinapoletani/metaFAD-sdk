<?php
class metafad_exporter_services_ead3exporter_utilities_Extractor extends PinaxObject
{
    public function extractPath($link)
    {
        $ar = $this->loadAr($link, 'archivi.models.ProduttoreConservatore');
        $type = $ar->tipologiaChoice;
        switch ($type) {
            case 'Ente':
                return './ead:corpname';
            case 'Persona':
                return './ead:persname';
            case 'Famiglia':
                return './ead:famname';
        }
    }

    public function extractFieldId($link)
    {
        $ar = $this->loadAr($link, 'archivi.models.ProduttoreConservatore');
        $identificativo = str_replace(' ', '_', $ar->identificativo);
        return $identificativo;
    }

    public function extractFieldDenominazione($link)
    {
        $ar = $this->loadAr($link, 'archivi.models.ProduttoreConservatore');
        $type = $ar->tipologiaChoice;
        switch ($type) {
            case 'Ente':
                return $ar->ente_famiglia_denominazione[0]->entitaDenominazione;
            case 'Persona':
                return $ar->persona_denominazione[0]->entitaDenominazione;
            case 'Famiglia':
                return $ar->famiglia_denominazione[0]->entitaDenominazione;
        }
    }

    public function extractStrumentiId($link)
    {
        $ar = $this->loadAr($link, "archivi.models.SchedaStrumentoRicerca");
        $identificativo = str_replace(' ', '_', $ar->identificativo);
        return $identificativo;
    }

    public function extractBiblioId($link)
    {
        $ar = $this->loadAr($link, "archivi.models.SchedaBibliografica");
        return $ar->identificativo;
    }

    public function extractBiblioText($link)
    {
        $ar = $this->loadAr($link, "archivi.models.SchedaBibliografica");
        return $ar->titoloLibroORivista . ' - ' . $ar->annoDiEdizione;
    }

    public function extractBiblioSBN($link)
    {

        $ar = $this->loadAr($link, "archivi.models.SchedaBibliografica");
        if (!$ar->rifSBN_url) {
            return '';
        }
        return $ar->titoloLibroORivista . ' - ' . $ar->annoDiEdizione;
    }

    public function extractBiblioSBNURI($link)
    {
        $ar = $this->loadAr($link, "archivi.models.SchedaBibliografica");
        return $ar->rifSBN_url;
    }

    public function extractFonteId($link)
    {
        $ar = $this->loadAr($link, "archivi.models.FonteArchivistica");
        return $ar->identificativo;
    }

    public function extractFonteText($link)
    {
        $ar = $this->loadAr($link, "archivi.models.FonteArchivistica");
        $str = $ar->titolo;
        if ($descrizione = $ar->descrizione) {
            $str .= " - $descrizione";
        }
        if ($segnatura = $ar->localizzazioneSegnatura) {
            $str .= " - $segnatura";
        }
        if ($osservazioni = $ar->osservazioni) {
            $str .= " - $osservazioni";
        }
        return $str;
    }

    public function extractFonteURI($link)
    {
        $ar = $this->loadAr($link, "archivi.models.FonteArchivistica");
        if (!$ar->riferimentiWeb[0]->url) {
            return '';
        }
        $str = $ar->titolo;
        if ($denominazioneSitoWeb = $ar->riferimentiWeb[0]->denominazioneSitoWeb) {
            $str .= " - $denominazioneSitoWeb";
        }
        if ($descrizione = $ar->riferimentiWeb[0]->descrizione) {
            $str .= " - $descrizione";
        }
        return $str;
    }

    public function extractFonteURIURI($link)
    {
        $ar = $this->loadAr($link, "archivi.models.FonteArchivistica");
        $uri = $ar->riferimentiWeb[0]->url;
        if (!$uri) {
            return '';
        }
        return $uri;
    }

    public function extractComplessoId($link)
    {
        $ar = $this->loadAr($link, "archivi.models.ComplessoArchivistico");
        $identificativo = str_replace(' ', '_', $ar->identificativo);
        return $identificativo;
    }

    public function extractComplessoDen($link)
    {
        $ar = $this->loadAr($link, "archivi.models.ComplessoArchivistico");
        return $ar->denominazione;
    }

    public function extractComplessoLevel($link)
    {
        $ar = $this->loadAr($link, "archivi.models.ComplessoArchivistico");
        $level = $ar->livelloDiDescrizione;
        $utility = pinax_ObjectFactory::createObject('metafad_exporter_services_ead3exporter_utilities_UtilitiesEAD3');
        return $utility->detectRecordLevel($level);
    }

    public function extractTematismi($link)
    {
        $ar = $this->loadAr($link, "archivi.models.ComplessoArchivistico");
        $tematismiArr = [];
        $tematismi = $ar->tematismo;
        foreach ($tematismi as $t) {
            $tematismiArr[] = $t->tematismoField;
        }
        if (count($tematismiArr)) {
            return $tematismiArr;
        }
        return '';
    }

    public function extractToponimoDenom($link)
    {
        $ar = $this->loadAr($link, "archivi.models.Toponimi");
        return $ar->nomeLuogo;
    }

    public function extractToponimoComuneAtt($link)
    {
        $ar = $this->loadAr($link, "archivi.models.Toponimi");
        return $ar->comuneAttuale;
    }

    public function extractToponimoDenomCoeva($link)
    {
        $ar = $this->loadAr($link, "archivi.models.Toponimi");
        return $ar->denominazioneCoeva;
    }

    public function extractAntroponimoDenom($link)
    {
        $ar = $this->loadAr($link, "archivi.models.Antroponimi");
        return $ar->cognome;
    }

    public function extractEnteDenom($link)
    {
        $ar = $this->loadAr($link, "archivi.models.Enti");
        return $ar->denominazioneEnte;
    }

    public function extractRedattorePath($compilazione)
    {
        $tipologiaRedattore = strtolower($compilazione->tipologiaRedattore);
        if ($tipologiaRedattore !== 'persona') {
            return './scons:agente/scons:denominazione';
        } else {
            return './scons:agente/scons:nome';
        }
    }

    public function extractRedattore($compilazione)
    {
        return $compilazione->redattore;
    }

    public function buildFELink($link, $params)
    {
        if (!$params->notLink) {
            $ar = $this->loadAr($link, $params->model);
            $id = $ar->codiceIdentificativoSistema;
        } else {
            $link  = explode(' ', $link);
            $id = end($link);
        }
        $FEurl = __Config::get('metafad.FE.url');
        $application = pinax_ObjectValues::get('org.pinax', 'application');
        $lan = $application->getLanguage();
        // TODO 185 e 186 sarsnno sostituiti con URL parlanti
        switch ($params->model) {
            case 'archivi.models.ComplessoArchivistico':
                $FElink = $FEurl . "/$lan/185/ricerca/detail/$id";
                break;
            case 'archivi.models.ProduttoreConservatore':
                $FElink = $FEurl . "/$lan/186/esplora/detailproduttoreconservatore/$id";
                break;
        }
        return $FElink;
    }

    /*Sezione di gestione delle date
    ****************************************************/
    public function extractDataERemoto($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        return $this->extractDate($cronologia, 'Remoto');
    }

    public function extractDataERecente($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        return $this->extractDate($cronologia, 'Recente');
    }

    public function extractPathCronologiaRemoto($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        $date = $this->extractDate($cronologia, 'Remoto');
        $typeDate = $this->extractTypeDate($cronologia, 'Remoto');
        if ($cronologia->tipoData === 'data-singola') {
            if ($typeDate == 'secolare') {
                return $this->manageDataSecolare($cronologia, $date, 'Remoto');
            }
            return "./ead:datesingle[@standarddate='$date']";
        } elseif ($cronologia->tipoData === 'data-intervallo' || $cronologia->tipoData === 'data-aperta' || !isset($cronologia->tipoData)) {
            if ($typeDate == 'secolare') {
                return $this->manageDataSecolare($cronologia, $date, 'Remoto');
            }
            return "./ead:daterange/ead:fromdate[@standarddate='$date']";
        }
    }

    public function extractPathCronologiaRecente($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        $date = $this->extractDate($cronologia, 'Recente');
        $typeDate = $this->extractTypeDate($cronologia, 'Recente');
        if ($cronologia->tipoData === 'data-singola') {
            if ($typeDate == 'secolare') {
                return $this->manageDataSecolare($cronologia, $date, 'Recente');
            }
            return "./ead:datesingle[@standarddate='$date']";
        } elseif ($cronologia->tipoData === 'data-intervallo' || !isset($cronologia->tipoData)) {
            if ($typeDate == 'secolare') {
                return $this->manageDataSecolare($cronologia, $date, 'Recente');
            }
            return "./ead:daterange/ead:todate[@standarddate='$date']";
        } elseif ($cronologia->tipoData === 'data-aperta') {
            return "./ead:daterange/ead:todate[@standarddate='2099-12-31']";
        }
    }

    private function manageDataSecolare($cronologia, $date, $type = '')
    {
        $specifica = $cronologia->{'estremo' . $type . '_specifica'};
        $integer = metafad_common_helpers_RomanService::romanToInteger($date);

        $path = $this->manageSpecifica($specifica, $integer, $cronologia->tipoData, $type);
        return $path;
    }

    private function checkIfArray($cronologia)
    {
        if (is_array($cronologia)) {
            return $cronologia[0];
        }
        return $cronologia;
    }

    private function extractDate($cronologia, $type)
    {
        $date = $cronologia->{'estremo' . $type . '_data'} ?: $cronologia->{'estremo' . $type . '_secolo'};
        $date = str_replace('/', '-', $date);
        if ($type == 'Recente' && $cronologia->tipoData == 'data-aperta') {
            $date = '2099-12-31';
        }
        return $date;
    }

    public function extractQualificaDataPersona($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        $qualifica = $cronologia->persona_qualificaData;
        $type = $this->extractTypeDate($cronologia, 'Remoto');
        if (!$qualifica) {
            return '';
        }
        $value = '';
        switch ($qualifica) {
            case 'Data di nascita':
                $value = 'dataDiNascita';
                break;
            case 'Data di morte':
                $value = 'dataDiMorte';
                break;
            case 'Data di attività':
                $value = 'dataDiAttivita';
        }
        if ($type == 'secolare') {
            $value = str_replace('data', 'secolo', $value);
        }
        return $value;
    }

    public function extractQualificaDataFamiglia($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        $qualifica = $cronologia->famiglia_qualificaData;
        $type = $this->extractTypeDate($cronologia, 'Remoto');
        if (!$qualifica) {
            return '';
        }
        $value = '';
        switch ($qualifica) {
            case 'Data di origine':
                $value = 'dataDiOrigine';
                break;
            case 'Data di estinzione':
                $value = 'dataDiEstinzione';
                break;
            case 'Data di attività':
                $value = 'dataDiAttivita';
        }
        if ($type == 'secolare') {
            $value = str_replace('data', 'secolo', $value);
        }
        return $value;
    }

    public function extractQualificaDataEnte($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        $qualifica = $cronologia->ente_famiglia_qualificaData;
        $type = $this->extractTypeDate($cronologia, 'Remoto');
        if (!$qualifica) {
            return '';
        }
        $value = '';
        switch ($qualifica) {
            case 'Data di cessazione/soppressione':
                $value = 'dataDiCessazioneSoppressione';
                break;
            case 'Data di istituzione':
                $value = 'dataDiIstituzione';
        }
        if ($type == 'secolare') {
            $value = str_replace('data', 'secolo', $value);
        }
        return $value;
    }

    public function extractValiditaProduttori($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        if ($cronologia->estremoRemoto_validita) {
            $qualifica = '';
            $qualifica = $this->extractQualificaDataPersona($cronologia);
            if ($qualifica) {
                $qualifica = ucfirst($qualifica);
                $validita = "validita$qualifica";
                return $validita;
            }
            return '';
        }
        return '';
    }

    public function extractValiditaProduttoriValue($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        if ($cronologia->estremoRemoto_validita) {
            return $cronologia->estremoRemoto_validita;
        }
        return '';
    }

    private function manageSpecifica($specifica, $integer, $tipoData, $type)
    {
        switch ($specifica) {
            case 'Fine':
                $integerB = $this->createSecularStandard($integer, 90, 'before');
                $integerNA = $this->createSecularStandard($integer, 100, 'notafter');
                break;
            case 'Inizio':
                $integerB = $this->createSecularStandard($integer, 1, 'before');
                $integerNA = $this->createSecularStandard($integer, 10, 'notafter');
                break;
            case 'Metà':
                $integerB = $this->createSecularStandard($integer, 46, 'before');
                $integerNA = $this->createSecularStandard($integer, 55, 'notafter');
                break;
            case 'Prima metà':
                $integerB = $this->createSecularStandard($integer, 0, 'before');
                $integerNA = $this->createSecularStandard($integer, 50, 'notafter');
                break;
            case 'Primo quarto':
                $integerB = $this->createSecularStandard($integer, 0, 'before');
                $integerNA = $this->createSecularStandard($integer, 25, 'notafter');
                break;
            case 'Seconda metà':
                $integerB = $this->createSecularStandard($integer, 51, 'before');
                $integerNA = $this->createSecularStandard($integer, 100, 'notafter');
                break;
            case 'Secondo quarto':
                $integerB = $this->createSecularStandard($integer, 26, 'before');
                $integerNA = $this->createSecularStandard($integer, 50, 'notafter');
                break;
            case 'Terzo quarto':
                $integerB = $this->createSecularStandard($integer, 51, 'before');
                $integerNA = $this->createSecularStandard($integer, 75, 'notafter');
                break;
            case 'Ultimo quarto':
                $integerB = $this->createSecularStandard($integer, 76, 'before');
                $integerNA = $this->createSecularStandard($integer, 100, 'notafter');
                break;
            default:
                $integerB = $this->createSecularStandard($integer, 1, 'before');
                $integerNA = $this->createSecularStandard($integer, 99, 'notafter');
                break;
        }
        if ($tipoData == 'data-singola') {
            $path = "./ead:datesingle[@notbefore='$integerB'][@notafter='$integerNA']";
        } elseif ($tipoData == 'data-intervallo' || $tipoData == 'data-aperta' || is_null($tipoData)) {
            if ($type == 'Recente') {
                $path = "./ead:daterange/ead:todate[@notbefore='$integerB'][@notafter='$integerNA']";
            } else {
                $path = "./ead:daterange/ead:fromdate[@notbefore='$integerB'][@notafter='$integerNA']";
            }
        }
        return $path;
    }

    private function createSecularStandard($integer, $toAdd, $type)
    {
        --$integer;
        $integer = str_pad($integer * 100 + $toAdd, 4, '0', STR_PAD_LEFT);
        return $type == 'before' ? $integer . '-01-01' : $integer . '-12-31';
    }

    private function extractTypeDate($cronologia, $type)
    {
        return $cronologia->{'estremo' . $type . '_data'} ? 'puntuale' : ($cronologia->{'estremo' . $type . '_secolo'} ? 'secolare' : '');
    }

    //Per schede diverse da CA, UA, UD e Produttori
    public function extractOtherDate($cronologia)
    {
        $cronologia = $this->checkIfArray($cronologia);
        $remoto = $this->extractDate($cronologia, 'Remoto');
        $recente = $this->extractDate($cronologia, 'Recente');
        $typeRemoto = $this->extractTypeDate($cronologia, 'Remoto');
        if ($typeRemoto == 'secolare') {
            $integer = metafad_common_helpers_RomanService::romanToInteger($remoto);
            $remoto = $this->createSecularStandard($integer, 1, 'before');
        }
        $typeRecente = $this->extractTypeDate($cronologia, 'Recente');
        if ($typeRecente == 'secolare') {
            $integer = metafad_common_helpers_RomanService::romanToInteger($recente);
            $recente = $this->createSecularStandard($integer, 1, 'before');
        }
        if ($cronologia->tipoData != 'data-intervallo') {
            return $this->padDate($remoto);
        } else {
            return $this->padDate($remoto) . '/' . $this->padDate($recente);
        }
    }

    public function extractType($cronologia)
    {
        $value = '';
        $type = $this->extractTypeDate($cronologia, 'Remoto');
        if ($cronologia->tipoData != 'data-intervallo') {
            $value = 'singleDate';
        } else {
            $value = 'rangeDate';
        }
        if ($type == 'secolare') {
            $value = str_replace('Date', 'Sec', $value);
        }
        return $value;
    }

    public function extractSpecificaSec($cronologia)
    {
        return strtolower($cronologia->estremoRemoto_specifica);
    }

    public function extractValidita($cronologia)
    {
        return strtolower($cronologia->estremoRemoto_validita);
    }

    private function padDate($date)
    {
        if ($date == '') {
            return '';
        }
        if (strlen($date) === 10) {
            return str_replace('-', '', $date);
        }
        $arr = explode('-', $date);
        if (strlen($arr[0] < 4)) {
            $arr[0] = str_pad($arr[0], 4, '0', STR_PAD_LEFT);
        }
        if (count($arr) === 1) {
            return $arr[0] . '0101';
        }
        if (count($arr) === 2) {
            return $arr[0] . $arr[1] . '01';
        }
        return $arr[0] . $arr[1] . $arr[2];
    }

    //***************************************************

    private function loadAr($link, $model)
    {
        $ar = pinax_ObjectFactory::createModel($model);
        $ar->load($link->id);
        return $ar;
    }
}
