<?php
class metafad_exporter_services_ead3exporter_utilities_Joiner extends PinaxObject
{
    public function altezzaLarghezzaJoiner($res, $id)
    {
        $ar = $this->loadAr($id, 'archivi.models.UnitaDocumentaria');
        $larghezza = $ar->descrizioneFisica_larghezza;
        if ($larghezza) {
            $res = "Altezza: $res, Larghezza: $larghezza";
        } else {
            $res = "Altezza: $res";
        }
        return $res;
    }

    public function luogoRappresentatoGraficaJoiner($res, $id)
    {
        $ar = $this->loadAr($id, 'archivi.models.UnitaDocumentaria');
        $comuneAtt = '';
        $denCoeva = '';
        if ($ar->grafica_luogo_comuneAttuale) {
            $comuneAtt = ', Comune attuale: ' . $ar->grafica_luogo_comuneAttuale;
        }
        if ($ar->grafica_luogo_denominazioneCoeva) {
            $denCoeva = ', Denominazione coeva: ' . $ar->grafica_luogo_denominazioneCoeva;
        }
        $res = "Stato: $res$comuneAtt$denCoeva";
        return $res;
    }

    public function luogoRappresentatoCartografiaJoiner($res, $id)
    {
        $ar = $this->loadAr($id, 'archivi.models.UnitaDocumentaria');
        $comuneAtt = '';
        $denCoeva = '';
        if ($ar->cartografia_luogo_comuneAttuale) {
            $comuneAtt = ', Comune attuale: ' . $ar->cartografia_luogo_comuneAttuale;
        }
        if ($ar->cartografia_luogo_denominazioneCoeva) {
            $denCoeva = ', Denominazione coeva: ' . $ar->cartografia_luogo_denominazioneCoeva;
        }
        $res = "Stato: $res$comuneAtt$denCoeva";
        return $res;
    }

    public function luogoRappresentatoFotoJoiner($res, $id)
    {
        $ar = $this->loadAr($id, 'archivi.models.UnitaDocumentaria');
        $comuneAtt = '';
        $denCoeva = '';
        if ($ar->foto_luogo_comuneAttuale) {
            $comuneAtt = ', Comune attuale: ' . $ar->foto_luogo_comuneAttuale;
        }
        if ($ar->cartografia_luogo_denominazioneCoeva) {
            $denCoeva = ', Denominazione coeva: ' . $ar->foto_luogo_denominazioneCoeva;
        }
        $res = "Stato: $res$comuneAtt$denCoeva";
        return $res;
    }

    public function condizioniAccessoJoiner($res, $id) {
        $ar = $this->loadAr($id, 'archivi.models.ProduttoreConservatore');
        if(count($ar->sog_cons_sedi) > 0) {
            $sedi = $ar->sog_cons_sedi[0];
            if($sedi->sog_cons_sedi_accessoDisabili) {
                $res .= '. Accesso disabili: ' . $sedi->sog_cons_sedi_accessoDisabili . '.';
            }
        }
        return $res;
    }

    private function loadAr($id, $model)
    {
        $ar = pinax_ObjectFactory::createModel($model);
        $ar->load($id);
        return $ar;
    }
}
