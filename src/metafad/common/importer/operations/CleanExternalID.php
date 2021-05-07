<?php

/**
 * Class metafad_common_importer_operations_CleanExternalID
 * Pulisce gli externalID dei record
 */
class metafad_common_importer_operations_CleanExternalID extends metafad_common_importer_operations_LinkedToRunner
{
    protected $filename;
    protected $xpath;
    protected $overwrite = false;
    protected $instituteKey;
    protected $archiviProxy;

    /**
     * @param stdClass $params
     * @param metafad_common_importer_MainRunner $runnerRef
     */
    function __construct($params, $runnerRef)
    {
        $this->filename = $params->filename ?: $this->filename;
        $this->overwrite = $params->overwrite == '1';
        $this->instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
        $this->archiviProxy = pinax_ObjectFactory::createObject("archivi_models_proxy_ArchiviProxy");
        $this->archiviProxy->setRetryWithDraftOnInvalidate(true);
        $this->archiviProxy->isImportProcess();
        parent::__construct($params, $runnerRef);
    }

    /**
     * @param stdClass $input
     * @return stdClass
     */
    function execute($input)
    {
        $dom = new DOMDocument();

        if ($this->overwrite) {
            if ($this->instituteKey == 'diplomatico-firenze') {
                $this->buildXpath($dom);
                $this->buildDiplomaticoRelations();
            }
            return $input;
        }

        try {
            $this->buildXpath($dom);
        } catch (Exception $ex) {
            if (!$this->suppressErrors) {
                throw $ex;
            }
        }

        //TODO è sufficente il controllo sull'assenza dell'href?
        $map = [
            'SC' => ["//scons/identificativi/identificativo/text()"],
            'SP' => ["//eac-cpf/control/recordId/text()"],
            'CA' => ["//did/unitid/@identifier"],
            'SR' => ["//ead/control/recordid/text()", "//ead/control/representation/@href"]
        ];
        $this->cleanExtID($map);
        if ($this->instituteKey == 'diplomatico-firenze') {
            $this->buildDiplomaticoRelations();
        }
        return $input;
    }

    function validateInput($input)
    {
        return "";
    }

    private function cleanExtID($map)
    {
        foreach ($map as $key => $value) {
            foreach ($value as $path) {
                $nodeList = $this->xpath->query($path);
                foreach ($nodeList as $node) {
                    $id = $node->textContent;
                    //$id = str_replace('_', ' ', $id);
                    $it = pinax_ObjectFactory::createModelIterator($this->getModel($key))->where('externalID', $id . '##current##');
                    foreach ($it as $ar) {
                        $ar->externalID = str_replace('##current##', '', $ar->externalID);
                        $ar->save(null, false, 'PUBLISHED');
                    }
                }
            }
        }
    }

    private function getModel($key)
    {
        switch ($key) {
            case 'SC':
                return "archivi.models.ProduttoreConservatore";
            case 'SP':
                return "archivi.models.ProduttoreConservatore";
            case 'CA':
                return "archivi.models.ComplessoArchivistico";
            case 'SR':
                return "archivi.models.SchedaStrumentoRicerca";
        }
    }

    private function buildDiplomaticoRelations()
    {
        $nodeList = $this->xpath->query("//did/unitid/@identifier");
        foreach ($nodeList as $node) {
            $id = $node->textContent;
            if (strpos($id, 'Provenienza-') === false) {
                continue;
            }
            $it = pinax_ObjectFactory::createModelIterator("archivi.models.ComplessoArchivistico")->where('externalID', $id);
            if (!$it->count()) {
                throw new Exception("Scheda complesso $id non importata");
            }
            $ar = $it->first();
            $doc = $ar->documentazioneArchivioCollegata[0];
            if ($doc && $doc->url) {
                $itUA = pinax_ObjectFactory::createModelIterator('archivi.models.UnitaArchivistica')->where('externalID', $doc->url);
                if (!$itUA->count()) {
                    throw new Exception("L'id diplomatico" . $doc->url . " non è stato individuato (complesso: " . $ar->getId());
                } else {
                    $ua = $itUA->first();
                    $docUA = $ua->documentazioneArchivioCollegata[0];
                    if ($docUA && $docUA->doc_url) {
                        $doc->url = (string)$ua->getId();
                        $dataCA = $ar->getRawData();
                        $dataCA->documentazioneArchivioCollegata = [$doc];
                        $dataCA->__id = $ar->document_id;
                        $dataCA->__model = "archivi.models.ComplessoArchivistico";
                        $docUA->doc_url = (string)$ar->getId();
                        $dataUA = $ua->getRawData();
                        $dataUA->documentazioneArchivioCollegata = [$docUA];
                        $dataUA->__id = $ua->document_id;
                        $dataUA->__model = 'archivi.models.UnitaArchivistica';
                        $res1 = $this->archiviProxy->save($dataCA);
                        $res2 = $this->archiviProxy->save($dataUA);
                        $ar->deleteStatus('OLD');
                        $ua->deleteStatus('OLD');
                    } else {
                        throw new Exception("La scheda unità " . $ua->getId() . " non ha l'id diplomatico in documentazione d'archivio collegata");
                    }
                }
            }
        }
    }

    private function buildXpath($dom)
    {
        $content = str_replace("xmlns=\"\"", '', file_get_contents($this->filename));
        //SE NON FUNZIONA QUALCOSA, ELIMINARE LA RIGHE SOTTOSTANTI
        $content = str_replace(["ead:", "eac-cpf:", "scons:"], ["", "", ""], $content);
        $dom->loadXML(str_replace("xmlns=", "xmlns:faked=", $content), LIBXML_BIGLINES);
        $this->xpath = new DOMXPath($dom);
    }
}
