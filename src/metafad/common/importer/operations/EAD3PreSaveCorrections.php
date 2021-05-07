<?php
class metafad_common_importer_operations_EAD3PreSaveCorrections extends metafad_common_importer_operations_EADPreSaveCorrections
{
    private $map;
    private $sias;

    function __construct($params, $runner)
    {
        $this->sias = ['archivio-di-stato-di-alessandria'];
        parent::__construct($params, $runner);
    }
    function execute($input)
    {
        $institute = metafad_usersAndPermissions_Common::getInstituteKey();
        $data = $this->corrections($input->data);
        $this->manageDoubleTitle($data);
        $this->manageSigillo($data);
        $this->manageUnitDate($data);
        $this->manageScrittura($data);
        $this->purgeCronologia($data);
        $this->correctOrdinamento($data);
        $this->stadioDocumento($data);
        if ($institute == 'diplomatico-firenze') {
            $this->cronologiaDiplomatico($data);
            $this->consistenzaDiplomatico($data);
            $this->tipologiaDiplomatico($data);
            $this->duplicateAntroponimi($data);
            $this->splitScopeContentDiplomatico($data);
            $this->conservatoreDiplomatico($data);
        }
        if (in_array($institute, $this->sias) || ($institute == 'sias' && substr($institute, 0, 4) == 'sias')) {
            $this->statoConservazioneSIAS($data);
        }
        return (object) array("data" => $data);
    }

    function manageDoubleTitle($data)
    {
        if ($data->titoloAttribuito && $data->denominazione) {
            unset($data->titoloAttribuito);
        }
    }

    function manageSigillo($data)
    {
        if ($data->sigillo || $data->materialeSigillo) {
            $data->descrizioneSigillo = $data->sigillo;
            $data->sigillo = 'Sì';
        }
    }

    function manageUnitDate($data)
    {
        if ($data->cronologiaUnitDate) {
            foreach ($data->cronologiaUnitDate as $unitDate) {
                $unitDate->tipoData = metafad_common_helpers_ImporterCommons::inferTipologiaData($unitDate);
                $data->cronologia[] = $unitDate;
            }
            unset($data->cronologiaUnitDate);
        }
    }

    function manageScrittura($data)
    {
        if ($data->bloccoScrittura) {
            $separators = [' / ', ';', ',', '.', '-'];
            $it = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusForms')
                ->load('findTerm', array('moduleId' => 'archivi-unitadocumentaria', 'fieldName' => 'linguaScrittura', 'level' => '1'));
            $terms = [];
            foreach ($it as $ar) {
                $terms[] = $ar->thesaurusdetails_key;
            }
            foreach ($data->bloccoScrittura as $scrittura) {
                foreach ($separators as $sep) {
                    $arr = explode($sep, $scrittura->linguaScrittura);
                    if (count($arr) > 1) {
                        break;
                    }
                }
                $scrittura->linguaScrittura = '';
                $lingua = strtolower(trim($arr[0]));
                foreach ($terms as $term) {
                    if ($lingua == strtolower($term)) {
                        $scrittura->linguaScrittura = $term;
                        array_shift($arr);
                        break;
                    }
                }
                if ($arr) {
                    $descrizione = implode(',', $arr);
                    $scrittura->descrizioneScrittura = trim($descrizione, ' .');
                }
            }
        }
    }

    function statoConservazioneSIAS($data)
    {
        if ($data->integrazioneDescrizione) {
            $data->condizioniMateriale = $data->integrazioneDescrizione;
            $data->integrazioneDescrizione = '';
        }
    }

    function purgeCronologia($data)
    {
        $cronologiaNew = [];
        $validita = '';
        $cronologie = $data->cronologia;
        foreach ($cronologie as $cronologia) {
            if ($cronologia->estremoRemoto_validita) {
                if ($cronologia->estremoRemoto_data || $cronologia->estremoRemoto_secolo) {
                    $cronologiaNew[] = $cronologia;
                } else {
                    $validita = $cronologia->estremoRemoto_validita;
                }
            } else {
                if ($validita) {
                    $cronologia->estremoRemoto_validita = $validita;
                    $validita = '';
                }
                $cronologiaNew[] = $cronologia;
            }
        }
        $data->cronologia = $cronologiaNew;
    }

    private function correctOrdinamento($data)
    {
        $ordinamento = $data->ordinamentoGlobale;
        if (is_numeric($ordinamento)) {
            return;
        }
        if (strpos($ordinamento, ',') !== false) {
            $data->ordinamentoGlobale = explode(',', $ordinamento)[1];
        } else {
            unset($data->ordinamentoGlobale);
        }
    }

    private function cronologiaDiplomatico($data)
    {
        if ($data->cronologiaDiplomatico) {
            $data->cronologia = $data->cronologiaDiplomatico;
            $data->cronologia[0]->estremoCronologicoTestuale = metafad_common_helpers_ImporterCommons::getCronologicoTestuale($data->cronologia[0]->estremoRemoto_data, $data->cronologia[0]->estremoRecente_data);
            $data->cronologia[0]->tipoData = metafad_common_helpers_ImporterCommons::inferTipologiaData($data->cronologia[0]);
            unset($data->cronologiaDiplomatico);
        }
    }

    private function consistenzaDiplomatico($data)
    {
        if ($data->consistenza) {
            $consistenza = $data->consistenza;
            foreach ($consistenza as $c) {
                if (strpos($c->tipologia, 'digitalizzate') !== false) {
                    $arr = explode('digitalizzate ', $c->tipologia);
                    $years = str_replace(['(', ')'], '', $arr[1]);
                    $c->tipologia = 'Pergamene digitalizzate';
                    if ($c->integrazioneDescrizione) {
                        $c->integrazioneDescrizione .= ". Anni: $years";
                    } else {
                        $c->integrazioneDescrizione = "Anni: $years";
                    }
                }
            }
        }
    }

    private function tipologiaDiplomatico($data)
    {
        if ($data->tipologiaDiplomatico) {
            $tipo = '';
            foreach ($data->tipologiaDiplomatico as $prop) {
                $tipo .= ", $prop";
            }
            $data->tipo = trim($tipo, ' ,');
            unset($data->tipologiaDiplomatico);
        }
    }

    private function stadioDocumento($data)
    {
        if ($data->pergamena_forma) {
            if ($data->pergamena_forma == 'Sospetto falso') {
                $data->osservazioni = 'Traditio: Sospetto falso.';
                unset($data->pergamena_forma);
            }
        }
    }

    private function duplicateAntroponimi($data)
    {
        if ($data->autoreResponsabile) {
            $authors = [];
            foreach ($data->autoreResponsabile as $key => $aut) {
                $author = $aut->autoreCognomeNome->id;
                if (!in_array($author, $authors)) {
                    $authors[] = $aut->autoreCognomeNome->id;
                } else {
                    unset($data->autoreResponsabile[$key]);
                    continue;
                }
                $obj = new StdClass();
                $obj->intestazione = $aut->autoreCognomeNome;
                $data->antroponimi[] = $obj;
            }
            $authorReindex = array_values($data->autoreResponsabile);
            $data->autoreResponsabile = $authorReindex;
        }
    }

    private function splitScopeContentDiplomatico($data)
    {
        $arr = [
            'stato di conservazione' => 'condizioniMateriale',
            'segni particolari:' => 'integrazioneDescrizione',
            'Data cronica:' => 'notaDatazione'
        ];
        $content = $data->contestoProvenienza_descrizione;
        if (strpos($content, 'sono presenti miniature') !== false) {
            $this->concatFields($data, 'Sono presenti miniauture', 'integrazioneDescrizione');
        }
        if ($content) {
            foreach ($arr as $key => $val) {
                $startPos = strpos($content, $key);
                if ($startPos !== false) {
                    $c = substr($content, $startPos);
                    if ($val == 'integrazioneDescrizione' || $key == 'Data cronica:') {
                        $c = ucfirst($c);
                    } else {
                        $c = str_replace($key, '', $c);
                    }
                    $endPos1 = strpos($c, '||');
                    if ($endPos1 !== false) {
                        $c = substr($c, 0, $endPos1);
                        $endPos2 = strpos($c, ';');
                        if ($endPos2 !== false && $key != 'Data cronica:') {
                            $c = substr($c, 0, $endPos2);
                        }
                    }
                    $endPos3 = strpos($c, ';');
                    if ($endPos3 !== false && $key != 'Data cronica:') {
                        $c = substr($c, 0, $endPos3);
                    }
                    if ($key == 'Data cronica:') {
                        if (is_array($data->cronologia) && isset($data->cronologia[0])) {
                            $this->concatFields($data, $c, $val);
                        }
                    } else {
                        $this->concatFields($data, $c,  $val);
                    }
                }
            }
            $data->contestoProvenienza_descrizione = '';
            $this->concatFields($data, $content, 'osservazioni');
        }
    }

    private function concatFields($data, $content, $dest)
    {
        $content = trim($content);
        if ($dest == 'notaDatazione') {
            $val = $data->cronologia[0]->notaDatazione ?: '';
        } else {
            $val = $data->$dest ?: '';
        }
        $val = trim($val, '.');
        $val .= ". $content.";
        $val = ltrim($val, " .");
        if ($dest == 'notaDatazione') {
            $data->cronologia[0]->notaDatazione = $val;
        } else {
            $data->$dest = $val;
        }
    }

    public function conservatoreDiplomatico($data)
    {
        if ($data->__model == 'archivi.models.ComplessoArchivistico') {
            $arcProxy = $this->getOrSetDefault("archiviProxy", pinax_ObjectFactory::createObject("archivi_models_proxy_ArchiviProxy"));
            $arcProxy->setRetryWithDraftOnInvalidate(true);
            $arcProxy->isImportProcess();
            $it = pinax_ObjectFactory::createModelIterator('archivi.models.ProduttoreConservatore')->where('externalID', 'ITASFI');
            if ($it->count()) {
                $conservatore = $it->first();
                $soggettoConservatore = new StdClass();
                $soggettoConservatore->id = $conservatore->getId();
                $soggettoConservatore->text = $arcProxy->extractTitleFromStdClass($conservatore->getRawData());
            } else {
                $dataC = new StdClass();
                $dataC->__model = "archivi.models.ProduttoreConservatore";
                $dataC->externalID = 'ITASFI';
                $dataC->instituteKey = metafad_usersAndPermissions_Common::setInstituteKey(metafad_usersAndPermissions_Common::getInstituteKey());
                $dataC->acronimoSistema = "ICAR";
                $dataC->tipologiaChoice = "Ente";
                $idCode = new StdClass();
                $idCode->codice = 'ITASFI';
                $dataC->altriCodiciIdentificativi = array($idCode);
                $dataC->externalID = $idCode->codice;
                $denominazione = new StdClass();
                $denominazione->entitaDenominazione = 'AS Firenze';
                $dataC->ente_famiglia_denominazione = array($denominazione);
                $res = $arcProxy->save($dataC);
                $soggettoConservatore = new StdClass();
                $soggettoConservatore->id = $res['set']['__id'];
                $soggettoConservatore->text = $arcProxy->extractTitleFromStdClass($dataC);
            }
            $data->soggettoConservatore = $soggettoConservatore;
            unset($arcProxy);
        }
    }
}
