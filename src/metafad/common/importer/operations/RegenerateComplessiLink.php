<?php

/**
 * Class metafad_common_importer_operations_RegenerateComplessiLink
 * Salva nuovamente le schede complesso per salvare correttamente le denominazioni delle schede entità
 */
class metafad_common_importer_operations_RegenerateComplessiLink extends metafad_common_importer_operations_LinkedToRunner
{
    protected $filename;
    protected $archiviProxy;
    protected $extIds;
    protected $url;
    protected $xpath;

    /**
     * @param stdClass $params
     * @param metafad_common_importer_MainRunner $runnerRef
     */
    function __construct($params, $runnerRef)
    {
        $this->filename = $params->filename ?: '';
        $this->url = $params->url ?: '';
        $this->archiviProxy = pinax_ObjectFactory::createObject("archivi_models_proxy_ArchiviProxy");
        $this->archiviProxy->setRetryWithDraftOnInvalidate(true);
        $this->archiviProxy->isImportProcess();
        $this->extIds = [];
        parent::__construct($params, $runnerRef);
    }

    /**
     * @param stdClass $input
     * @return stdClass
     */
    function execute($input)
    {
        $dom = new DOMDocument();
        try {
            $this->buildXpath($dom);
        } catch (Exception $ex) {
            if (!$this->suppressErrors) {
                throw $ex;
            }
        }
        $this->extractExternalId();
        $this->regenerateLinks();
        return $input;
    }

    function extractExternalId()
    {
        $nodeList = $this->xpath->query("//did/unitid/@identifier");
        foreach ($nodeList as $node) {
            $this->extIds[] = $node->nodeValue;
        }
    }

    function regenerateLinks()
    {
        foreach ($this->extIds as $extId) {
            $it = __ObjectFactory::createModelIterator('archivi.models.ComplessoArchivistico')->where('instituteKey', metafad_usersAndPermissions_Common::getInstituteKey())->where('externalID', $extId);
            foreach ($it as $ar) {
                $caData = $ar->getRawData();
                if ($caData->soggettoConservatore) {
                    $idCons = $caData->soggettoConservatore->id;
                    $arCons = pinax_ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
                    if ($arCons->load($idCons)) {
                        $denCons = $arCons->_denominazione;
                        $caData->soggettoConservatore->text = $denCons;
                    }
                }
                if ($caData->produttori) {
                    $idsProd = [];
                    foreach ($caData->produttori as $linkProd) {
                        $idsProd[] = $linkProd->soggettoProduttore->id;
                    }
                    foreach ($idsProd as $idPr) {
                        $arProd = pinax_ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
                        if ($arProd->load($idPr)) {
                            $denProd = $arProd->_denominazione;
                            foreach ($caData->produttori as $linkProd) {
                                if ($linkProd->soggettoProduttore->id == $idPr) {
                                    $linkProd->soggettoProduttore->text = $denProd;
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($caData->strumentiRicerca) {
                    $idsStrumento = [];
                    foreach ($caData->strumentiRicerca as $linkStrumento) {
                        $idsStrumento[] = $linkStrumento->linkStrumentiRicerca->id;
                    }
                    foreach ($idsStrumento as $idStr) {
                        $arStrumento = pinax_ObjectFactory::createModel('archivi.models.SchedaStrumentoRicerca');
                        if ($arStrumento->load($idStr)) {
                            $denStrumento = $arStrumento->_denominazione;
                            foreach ($caData->strumentiRicerca as $linkStrumento) {
                                if ($linkStrumento->linkStrumentiRicerca->id == $idStr) {
                                    $linkStrumento->linkStrumentiRicerca->text = $denStrumento;
                                    break;
                                }
                            }
                        }
                    }
                }
                $caData->__id = $ar->getId();
                $caData->__model = 'archivi.models.ComplessoArchivistico';
                $res = $this->archiviProxy->save($caData);
                $ar->deleteStatus('OLD');
            }
        }
    }

    function validateInput($input)
    {
        return "";
    }

    private function buildXpath($dom)
    {
        if ($this->filename) {
            $content = str_replace("xmlns=\"\"", '', file_get_contents($this->filename));
            $content = str_replace(["ead:", "eac-cpf:", "scons:"], ["", "", ""], $content);
        } elseif ($this->url) {
            $curlSES = curl_init();
            curl_setopt($curlSES, CURLOPT_URL, $this->url);
            curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlSES, CURLOPT_HEADER, false);
            $content = curl_exec($curlSES);
            curl_close($curlSES);
            $content = str_replace("xmlns=\"\"", '', $content);
            $content = str_replace(["ead_icar:", "eac-cpf_icar:", "scons_icar:"], ["", "", ""], $content);

        }
        //SE NON FUNZIONA QUALCOSA, ELIMINARE LA RIGHE SOTTOSTANTI
        $dom->loadXML(str_replace("xmlns=", "xmlns:faked=", $content));
        $this->xpath = new DOMXPath($dom);
    }
}
