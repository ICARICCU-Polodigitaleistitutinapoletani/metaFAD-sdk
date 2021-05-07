<?php

class metafad_common_importer_operations_RelationsValidator extends PinaxObject
{
    protected $xml;
    protected $xpath;
    protected $logFile;
    protected $soggConsPaths;
    protected $soggProdPaths;
    protected $complPaths;
    protected $struPaths;
    protected $soggConsIdPath;
    protected $soggProdIdPath;
    protected $complIdPath;
    protected $struIdPath;
    protected $internalIdSearch;
    protected $lineNumber;

    function __construct($xml, $logFile, $internalIdSearch)
    {
        $this->xml = $xml;
        $this->xpath = new DOMXPath($xml);
        $this->logFile = $logFile;
        $this->internalIdSearch = $internalIdSearch;
        $this->lineNumber = [];
        $this->soggConsPaths = [
            "//did/repository/corpname/@identifier",
            "//did/repository/famname/@identifier",
            "//did/repository/persname/@identifier",
            "//scons/relazioni/relazione[@tipo='CONS']/text()"
        ];
        $this->soggProdPaths = [
            "//did/origination/corpname/@identifier",
            "//did/origination/famname/@identifier",
            "//did/origination/persname/@identifier",
            "//eac-cpf/relations/cpfRelation/relationEntry[@localType='soggettoProduttore']/text()"
        ];
        $this->complPaths = [
            "//scons/relazioni/relazione[@tipo='COMPL']/text()",
            "//eac-cpf/relations/resourceRelation[@resourceRelationType='creatorOf']/relationEntry[@localType='complesso']/text()",
            "//ead/control/localcontrol[@localtype='complArchCollegato']/term/@identifier"
        ];
        //TODO è sufficente il controllo sull'assenza dell'href?
        $this->struPaths = [
            "//otherfindaid/archref/ref[not(@href)]/text()",
            "//otherfindaid/archref/ref/@href"
        ];
        $this->soggConsIdPath = ["//scons/identificativi/identificativo[@tipo='ICAR']/text()"];
        $this->soggProdIdPath = ["//eac-cpf/control/recordId/text()"];
        $this->complIdPath = ["//did/unitid[@localtype='metaFAD']/@identifier"];
        $this->struIdPath = ["//ead/control/recordid/text()", "//ead/control/representation/@href"];
    }

    function validate()
    {
        $idsRel = [];
        $ids = [];
        //L'ordine è soggetto conservatore, soggetto produttore, complessi, strumenti
        $arrayPaths = [$this->soggConsPaths, $this->soggProdPaths, $this->complPaths, $this->struPaths];
        while (count($arrayPaths)) {
            $relPaths = array_shift($arrayPaths);
            $idsRel[] = $this->readRelations($relPaths);
        }
        $arrayIdPaths = [$this->soggConsIdPath, $this->soggProdIdPath, $this->complIdPath, $this->struIdPath];
        while (count($arrayIdPaths)) {
            $idPaths = array_shift($arrayIdPaths);
            $ids[] = $this->readRelations($idPaths);
        }
        $notFoundedIds = $this->checkRelations($idsRel, $ids);
        if ($this->internalIdSearch) {
            $notFoundedIds = $this->internalSearch($notFoundedIds);
        }
        return $this->generateLog($notFoundedIds);
    }

    private function readRelations($paths)
    {
        $ids = [];
        foreach ($paths as $path) {
            $nodeList = $this->xpath->query($path);
            foreach ($nodeList as $node) {
                $ids[] = str_replace('_', ' ', $node->textContent);
                $this->lineNumber[$node->textContent] = $node->getLineNo();
            }
        }
        return $ids;
    }

    private function checkRelations($idsRel, $ids)
    {
        $notFoundedIds = [];
        for ($i = 0; $i < 4; ++$i) {
            $notFounded = array_diff($idsRel[$i], $ids[$i]);
            foreach ($notFounded as $id) {
                $notFoundedIds[$i][] = $id;
            }
        }
        return $notFoundedIds;
    }

    private function internalSearch($notFoundedIds)
    {
        foreach ($notFoundedIds as $key => $ids) {
            foreach ($ids as $k => $id) {
                $record = pinax_ObjectFactory::createModelIterator($this->getModel($key))
                    ->where('externalID', $id);
                if ($record->count() > 0) {
                    unset($notFoundedIds[$key][$k]);
                } else {
                    $ar = pinax_ObjectFactory::createModel($this->getModel($key));
                    if ($ar->load(explode(' ', $id)[2])) {
                        unset($notFoundedIds[$key][$k]);
                    }
                }
            }
            return $notFoundedIds;
        }
    }

    private function generateLog($notFoundedIds)
    {
        $idsMap = [];
        foreach ($notFoundedIds as $key => $ids) {
            foreach ($ids as $id) {
                $idsMap[$this->getType($key)][] = $id;
            }
        }
        if (count($idsMap) == 0) {
            return false;
        }
        if (!file_exists('./export/icar-import_log_folder')) {
            mkdir('./export/icar-import_log_folder', 0777);
        }
        error_log("Rigo documento\tXpath\tTipo\tMessaggio\n", 3, $this->logFile);
        foreach ($idsMap as $key => $ids) {
            foreach ($ids as $id) {
                $line = $this->lineNumber[$id];
                error_log("$line\t\tERROR\tLa scheda con identificativo è $id è definita nelle relazioni ma è assente dal documento\n", 3, $this->logFile);
            }
        }
        return true;
    }

    private function getModel($key)
    {
        switch ($key) {
            case 0:
                return "archivi.models.ProduttoreConservatore";
            case 1:
                return "archivi.models.ProduttoreConservatore";
            case 2:
                return "archivi.models.ComplessoArchivistico";
            case 3:
                return "archivi.models.SchedaStrumentoRicerca";
        }
    }

    private function getType($key)
    {
        switch ($key) {
            case 0:
                return "Soggetto conservatore";
            case 1:
                return "Soggetto produttore";
            case 2:
                return "Complesso archivistico";
            case 3:
                return "Strumento di ricerca";
        }
    }
}
