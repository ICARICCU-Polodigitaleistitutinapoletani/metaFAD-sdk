<?php

class metafad_exporter_services_ead3exporter_utilities_UtilitiesEAD3 extends PinaxObject
{
    public function detectRecordLevel()
    {
        switch (func_get_arg(0)) {
            case 'unita-documentaria':
                return 'item';
            case 'documento-principale':
                return 'item';
            case 'unita':
                return 'file';
            case 'fondo':
                return 'fonds';
            case 'documento-allegato':
                return 'item';
            case 'sottounita':
                return 'otherlevel';
            case 'sottosottounita':
                return 'otherlevel';
            case 'serie':
                return 'series';
            case 'sottoserie':
                return 'subseries';
            case 'sottosottoserie':
                return 'subseries';
            case 'sub-fondo':
                return 'subfonds';
            case 'sezione':
                return 'subfonds';
            case 'superfondo':
                return 'recordgrp';
            case 'complesso-di-fondi':
                return 'recordgrp';
            case 'collezione-raccolta':
                return 'collection';
        }
    }

    public function detectRecordOtherLevel()
    {
        $arg = func_get_arg(0);
        if ($arg === 'sottounita' || $arg === 'sottosottounita') {
            return 'sottofascicolo';
        }
        return null;
    }

    public function charsSubstitute()
    {
        $charsToRep = func_get_arg(1)->charsToReplace;
        $newChar = func_get_arg(1)->newChar;
        return str_replace($charsToRep, $newChar, func_get_arg(0));
    }

    public function addTimeString()
    {
        if (!func_get_arg(0)) {
            return;
        }
        $params = new StdClass();
        $params->charsToReplace = '/';
        $params->newChar = '-';
        $date = $this->charsSubstitute(func_get_arg(0), $params);
        return $date . 'T00:00:00';
    }

    public function generateIsoDate()
    {
        $date = date('c');
        if ($pos = strpos($date, '+')) {
            $date = substr($date, 0, $pos);
        }
        return $date;
    }

    public function generateIsoDateIfNotDefined()
    {
        $dd = func_get_arg(0);
        if(!func_get_arg(0)) {
            return $this->generateIsoDate();
        }
        return func_get_arg(0);
    }

    public function strlower()
    {
        return strtolower(func_get_arg(0));
    }

    public function lowerfirst()
    {
        return lcfirst(func_get_arg(0));
    }

    public function emptyValue() {
        return '';
    }

    public function setConstantIfValueExixts()
    {
        if (func_get_arg(0)) {
            return func_get_arg(1)->constant;
        }
        return null;
    }

    public function addPrefix() {
        return func_get_arg(1)->prefix . func_get_arg(0);
    }

    public function detectComplessoPrecedente()
    {
        $id = func_get_arg(0);
        $ar = pinax_ObjectFactory::createModel("archivi.models.ComplessoArchivistico");
        $ar->load($id);
        $ordinamentoProvvisorio = $ar->ordinamentoProvvisorio;
        $parentId = (string) $ar->parent['id'];
        $children = pinax_ObjectFactory::createModelIterator("archivi.models.ComplessoArchivistico")->where('parent', $parentId);
        $closestOrd = 0;
        foreach ($children as $c) {
            $op = $c->ordinamentoProvvisorio;
            if ($op === $ordinamentoProvvisorio) {
                continue;
            }
            if ($op < $ordinamentoProvvisorio && $op > $closestOrd) {
                $closestOrd = $op;
                $identificativo = $c->identificativo;
            } else {
                continue;
            }
            if ($ordinamentoProvvisorio - $closestOrd == 1) {
                break;
            }
        }
        if ($closestOrd !== 0) {
            return $identificativo;
        }
        return null;
    }

    public function addLabel()
    {
        return func_get_arg(1)->label . ': ' . func_get_arg(0);
    }

    public function collapseOrariObject()
    {
        $orari = func_get_arg(0);
        $res = [];
        $resString = '';
        foreach ($orari as $orario) {
            $giorno = '';
            $orarioInizio = '';
            $orarioFine = '';
            $note = '';
            $descrizione = '';
            if ($orario->sog_cons_sedi_giornoSettimana) {
                $giorno = $orario->sog_cons_sedi_giornoSettimana;
            }
            if ($orario->orarioInizio) {
                $orarioInizio = "dalle " . $orario->orarioInizio;
            }
            if ($orario->sog_cons_sedi_orarioFine) {
                $orarioFine = "alle " . $orario->sog_cons_sedi_orarioFine . ".";
            }
            if ($orario->sog_cons_sedi_note) {
                $note = "Note: " . $orario->sog_cons_sedi_note . ".";
            }
            if ($orario->sog_cons_sedi_descrizione) {
                $descrizione = "Descrizione: " . $orario->sog_cons_sedi_descrizione . ".";
            }
            $str = trim("$giorno $orarioInizio $orarioFine $note $descrizione", ' ');
            if ($str) {
                $res[] = $str;
            }
        }
        foreach ($res as $r) {
            $resString .= "$r ";
        }
        return trim($resString, ' ');
    }

    public function collapseServiziObject()
    {
        $servizi = func_get_arg(0);
        $res = [];
        $resString = '';
        foreach ($servizi as $servizio) {
            $denominazione = '';
            $note = '';
            if ($servizio->denominazione) {
                $denominazione = $servizio->denominazione . '.';
            }
            if ($servizio->sog_cons_sedi_noteErogazioneServizio) {
                $note = $servizio->sog_cons_sedi_noteErogazioneServizio . '.';
            }
            $str = trim("$denominazione $note", ' ');
            if ($str) {
                $res[] = $str;
            }
        }
        foreach ($res as $r) {
            $resString .= "$r ";
        }
        return trim($resString, ' ');
    }

    public function detectTipologiaSede()
    {
        $value = strtolower(func_get_arg(0));
        if (func_get_arg(1) !== null) {
            $attribute = func_get_arg(1)->attribute;
        }
        switch ($value) {
            case 'sede principale':
                return $attribute == 'principale' ? 'S' : ($attribute == 'identificativo' ? 1 : 'N');
            case 'sede per consultazione':
                return $attribute == 'consultazione' ? 'S' : 'N';
            default:
                return 'N';
        }
    }

    public function buildDenominazione()
    {
        $obj = func_get_arg(0);
        $tipologiaSede = strtolower($obj->sog_cons_sedi_tipologia);
        $denominazione = '';
        $denominazione = $obj->sog_cons_sedi_denominazione;
        if ($tipologiaSede === 'sede sussidiaria' || $tipologiaSede === 'sede per la didattica') {
            $denominazione .= '. ' . ucfirst($tipologiaSede);
        }
        return $denominazione;
    }

    public function tipologiaChoiceProduttore()
    {
        $tipologia = strtolower(func_get_arg(0));
        switch ($tipologia) {
            case 'ente':
                return 'corporateBody';
            case 'famiglia':
                return 'family';
            case 'persona':
                return 'person';
        }
    }

    public function qualificaDenominazioneProduttore()
    {
        $qualifica = strtolower(func_get_arg(0));
        if ($qualifica != 'altre denominazioni') {
            return '';
        }
        return 'altraDenominazione';
    }

    public function generePersona()
    {
        $genere = strtolower(func_get_arg(0));
        switch ($genere) {
            case 'maschile':
                return 'maschile';
            case 'femminile':
                return 'femminile';
            default:
                return '';
        }
    }

    public function noteDatazioneProduttori()
    {
        $object = func_get_arg(0);
        $cronologia = $object->{func_get_arg(1)->cronologia};
        $qualifica = func_get_arg(1)->qualifica;
        $qualificaValue = func_get_arg(1)->qualificaValue;
        $nota = func_get_arg(1)->nota;
        if ($object->$qualifica != $qualificaValue) {
            return '';
        }
        return $cronologia[0]->$nota;
    }

    public function autoreStrumento()
    {
        $object = func_get_arg(0);
        $cognome = '';
        $nome = '';
        if ($object->cognomeAutore) {
            $cognome = $object->cognomeAutore . ', ';
            if ($object->nomeAutore) {
                $nome = $object->nomeAutore;
            }
        }
        return trim("$cognome$nome", ' ,');
    }

    public function thesaurusStato()
    {
        $stato = func_get_arg(0);
        $dictionary = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusDetails')
            ->where('thesaurus_code', 'VC_Localizzazione')
            ->where('thesaurusdetails_key', $stato);
        foreach ($dictionary as $ar) {
            $statoCode = $ar->thesaurusdetails_value;
        }
        return $statoCode;
    }

    public function getMetsId() {
        $id = str_replace(' ', '_', func_get_arg(0));
        $prefix = func_get_arg(1)->prefix;
        return $prefix . $id;
    }
}
