<?php
class metafad_common_importer_operations_ValidateFields extends metafad_common_importer_operations_LinkedToRunner
{
    protected $dom;
    protected $warningFile;
    protected $xpath;
    protected $pathsValidation;
    protected $configXpath;
    protected $fatalError;
    protected $fileLogger;
    protected $renameLog;
    //protected $firstLine;

    function __construct($dom, $warningFile, $xpath, $pathsValidation, $configFilePath, $renameLog = false)
    {
        $this->dom = $dom;
        $this->warningFile = $warningFile;
        $this->xpath = $xpath;
        $this->pathsValidation = $pathsValidation;
        $this->renameLog = $renameLog;
        $configDom = new DOMDocument();
        if ($configFilePath) {
            $configDom->load($configFilePath);
        } else {
            $conf = pinax_ObjectFactory::createObject('metafad_common_importer_utilities_GetConfig')->getConfig();
            $configDom->loadXML($conf);
        }
        $this->configXpath = new DOMXPath($configDom);
        $this->configXpath->registerNamespace("php", "http://php.net/xpath");
        $this->configXpath->registerPHPFunctions('strtolower');
        $this->fileLogger = pinax_ObjectFactory::createObject('metafad_common_importer_utilities_ImportFileLogger', $warningFile);
        //$this->firstLine = true;
    }

    function doValidation()
    {
        $this->validateXpath();
        $this->validateDefault();
        $this->validateVocabulary();
        $this->validateTracciatiSpecifici();
        $this->validateDAONumber();
        $this->validateDate();
        if ($this->renameLog && $this->fatalError) {
            $this->renameLogFile();
        }
        return $this->fatalError;
    }

    function validateXpath()
    {
        $paths = json_decode(file_get_contents($this->pathsValidation));
        $curLevel = '';
        foreach ($this->dom->getElementsByTagName('*') as $node) {
            $pathExists = false;
            $path = $node->getNodePath();
            if (($lvl = $this->detectCurrentLevel($node)) !== false) {
                $curLevel = $lvl;
            }
            $path = preg_replace("/\[\d*\]/", '', $path);
            if (strpos($path, '/ead/') !== false || strpos($path, '/ead:ead/') !== false) {
                if (substr($path, -2) == '/c' || substr($path, -9) == '/archdesc' || substr($path, -8) == '/control' || substr($path, -4) == '/dsc') {
                    continue;
                }
                $path = str_replace('/ead:', '/', $path);
                if (($pos = strrpos($path, '/control/')) !== false) {
                    $path = '/ead' . substr($path, $pos);
                } elseif (($pos = strrpos($path, '/c/')) !== false) {
                    $path = substr($path, $pos);
                    $path = str_replace('/c/', '/ead//', $path);
                } elseif (($pos = strrpos($path, '/archdesc/')) !== false) {
                    $path = substr($path, $pos);
                    $path = str_replace('/archdesc/', '/ead//', $path);
                }
            } elseif (strpos($path, '/eac-cpf/') !== false || strpos($path, '/eac-cpf:eac-cpf/') !== false) {
                $path = str_replace('/eac-cpf:', '/', $path);
                $path = substr($path, strpos($path, '/eac-cpf/'));
                $curLevel = '';
            } elseif (strpos($path, '/scons/') !== false || strpos($path, '/scons:scons/') !== false) {
                $path = str_replace('/scons:', '/', $path);
                $path = substr($path, strpos($path, '/scons/'));
                $curLevel = '';
            } else {
                continue;
            }
            $originalPath = $path;
            foreach ($paths as $p => $attrNames) {
                $originalP = $p;
                if (strpos($p, '##')) {
                    $p = explode('##', $p)[0];
                    //list($p, $type) = explode('##', $p);
                }
                if (strpos($p, $path) !== false) {
                    if (!$this->checkLevelCoherence($curLevel, $originalP, $originalPath, $node)) {
                        break;
                    }
                    if (property_exists($attrNames, '#equal#')) {
                        if ($path !== $p) {
                            $pathExists = false;
                            continue;
                        }
                    }
                    $pathExists = true;
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attr) {
                            $attrExists = false;
                            $path = $originalPath . "[@" . $attr->nodeName . "='" . $attr->nodeValue . "']";
                            foreach ($attrNames as $name => $attrValues) {
                                if ($attr->nodeName === $name) {
                                    foreach ($attrValues as $val) {
                                        $originalval = $val;
                                        if (strpos($val, '##')) {
                                            $val = explode('##', $val)[0];
                                            //list($val, $type) = explode('##', $val);
                                        }
                                        if ($val === '#allValues#' || $attr->nodeValue === $val) {
                                            $attrExists = true;
                                            $this->checkLevelCoherence($curLevel, $originalP, $originalPath, $node, $originalval);
                                            break 2;
                                        }
                                    }
                                }
                            }
                            if (!$attrExists)
                                $this->fileLogger->writeLogLine('WARNING', $path, "L'elemento non è mappato", $node);
                        }
                    }
                    break;
                } elseif (strpos($path, $p) !== false) {
                    if (property_exists($attrNames, '#onlyFather#')) {
                        $pathExists = true;
                        break;
                    }
                }
            }
            if (!$pathExists) {
                $this->fileLogger->writeLogLine('WARNING', $path, "L'elemento non è mappato", $node);
            }
        }
    }

    function validateDefault()
    {
        $types = ['ead3', 'ead3_strumenti', 'scons2', 'eac-cpf'];
        foreach ($types as $type) {
            $bibliografia_tipologiaSpecifica_node = $this->configXpath->query("/root/$type/mapping/bibliografia_tipologiaSpecifica");
            $this->verifyDefault($bibliografia_tipologiaSpecifica_node, "/root/$type/mapping/bibliografia_tipologiaSpecifica");
        }
        foreach ($types as $type) {
            $bibliografia_annoDiEdizione_node = $this->configXpath->query("/root/$type/mapping/bibliografia_annoDiEdizione");
            $this->verifyDefault($bibliografia_annoDiEdizione_node, "/root/$type/mapping/bibliografia_annoDiEdizione");
        }

        $condizioneGiuridica_condizioneGiuridica = $this->configXpath->query("/root/eac-cpf/mapping/condizioneGiuridica_condizioneGiuridica");
        $this->verifyDefault($condizioneGiuridica_condizioneGiuridica, "/root/eac-cpf/mapping/condizioneGiuridica_condizioneGiuridica");
    }

    function validateVocabulary()
    {
        //ead3 gerarchico
        $compilazione_azioneNodeEad3 = $this->xpath->query("//ead//processinfo[@localtype='compilatori']/processinfo[@localtype='compilatore']/p/persname/part[@localtype='tipoIntervento']");
        $this->verifyVocabulary($compilazione_azioneNodeEad3, "/ead//processinfo[@localtype='compilatori']/processinfo[@localtype='compilatore']/p/persname/part[@localtype='tipoIntervento']", "ead3", "compilazione_azione");
        $tracciatiSpecifici = $this->xpath->query("//ead//controlaccess/genreform[not(@*)]/part");
        $this->verifyVocabulary($tracciatiSpecifici, "/ead//controlaccess/genreform[not(@*)]/part/", "ead3", "tracciatiSpecifici");
        $linguaEScrittura = $this->xpath->query("//ead//did/physdescstructured/physfacet[@localtype='scrittura']");
        $this->verifyVocabulary($linguaEScrittura, "/ead//did/physdescstructured/physfacet[@localtype='scrittura']", "ead3", "linguaEScrittura");
        $livelloDescrizione = $this->xpath->query("//ead//*/@otherlevel");
        $this->verifyVocabulary($livelloDescrizione, "/ead//*/@otherlevel", "ead3", "livelloDescrizione", ['sottofascicolo']);
        $stadioDocumento = $this->xpath->query("//ead//controlaccess/genreform[@localtype='qualitaAtto']/part");
        $this->verifyVocabulary($stadioDocumento, "/ead//controlaccess/genreform[@localtype='qualitaAtto']/part", "ead3", "stadioDocumento");
        $validita = $this->xpath->query("//ead//did/unitdatestructured/@certainty");
        $this->verifyVocabulary($validita, "/ead//did/unitdatestructured/@certainty", "ead3", "validita");
        $autore_ruolo = $this->xpath->query("//ead//controlaccess/persname/@relator");
        $this->verifyVocabulary($autore_ruolo, "/ead//controlaccess/persname/@relator", "ead3", "autore_ruolo");

        //soggetto conservatore
        $denominazione_qualificaDellaDenominazione = $this->xpath->query("//scons//denominazione[@qualifica!='acronimo']/@qualifica");
        $this->verifyVocabulary($denominazione_qualificaDellaDenominazione, "/scons//denominazione[@qualifica!='acronimo']/@qualifica", "scons2", "denominazione_qualificaDellaDenominazione");
        $tipologiaEnte = $this->xpath->query("//scons//tipologia");
        $this->verifyVocabulary($tipologiaEnte, "/scons//tipologia", "scons2", "tipologiaEnte", ["ente", "famiglia", "persona"]);
        $compilazione_azioneSC = $this->xpath->query("//scons//info/evento/@tipoEvento");
        $this->verifyVocabulary($compilazione_azioneSC, "/scons//info/evento/@tipoEvento", "scons2", "compilazione_azione");
        $soggettiConservatori_tipoRelazione = $this->xpath->query("//scons//relazioni/relazione[@tipo='CONS']/@tipoRelSC");
        $this->verifyVocabulary($soggettiConservatori_tipoRelazione, "/scons//relazioni/relazione[@tipo='CONS']/@tipoRelSC", "scons2", "soggettiConservatori_tipoRelazione");

        //soggetto produttore
        $compilazione_azioneP = $this->xpath->query("//eac-cpf//control/maintenanceHistory/maintenanceEvent/eventType");
        $this->verifyVocabulary($compilazione_azioneP, "/eac-cpf//control/maintenanceHistory/maintenanceEvent/eventType", "eac-cpf", "compilazione_azione");
        $compilazione_tipologiaRedattoreSP = $this->xpath->query("//eac-cpf//control/maintenanceHistory/maintenanceEvent/agentType");
        $this->verifyVocabulary($compilazione_tipologiaRedattoreSP, "/eac-cpf//control/maintenanceHistory/maintenanceEvent/agentType", "eac-cpf", "compilazione_tipologiaRedattore");
        $tipologia_tipologiaSP = $this->xpath->query("//eac-cpf//cpfDescription/identity/entityType");
        $this->verifyVocabulary($tipologia_tipologiaSP, "/eac-cpf//cpfDescription/identity/entityType", "eac-cpf", "tipologia_tipologia");
        $denominazione_qualificaDellaDenominazione = $this->xpath->query("//eac-cpf//cpfDescription/identity/nameEntry/@localType");
        $this->verifyVocabulary($denominazione_qualificaDellaDenominazione, "/eac-cpf//cpfDescription/identity/nameEntry/@localType", "eac-cpf", "denominazione_qualificaDellaDenominazione");
        $ente_luogo_qualificaLuogo = $this->xpath->query("//eac-cpf//cpfDescription/description/place/placeRole");
        $this->verifyVocabulary($ente_luogo_qualificaLuogo, "/eac-cpf//cpfDescription/description/place/placeRole", "eac-cpf", "ente_luogo_qualificaLuogo");
        $condizioneGiuridica_condizioneGiuridica = $this->xpath->query("//eac-cpf//cpfDescription/description/legalStatuses/legalStatus/term");
        $this->verifyVocabulary($condizioneGiuridica_condizioneGiuridica, "/eac-cpf//cpfDescription/description/legalStatuses/legalStatus/term", "eac-cpf", "condizioneGiuridica_condizioneGiuridica");
        $tipologia_tipologiaEnte = $this->xpath->query("//eac-cpf//cpfDescription/description/localDescription[@localType='tipologiaEnte']/term");
        $this->verifyVocabulary($tipologia_tipologiaEnte, "/eac-cpf//cpfDescription/description/localDescription[@localType='tipologiaEnte']/term", "eac-cpf", "tipologia_tipologiaEnte");

        //strumenti di ricerca
        $tipologia_tipologiaSR = $this->xpath->query("//ead//control/filedesc/editionstmt/edition[@localtype='typology']");
        $this->verifyVocabulary($tipologia_tipologiaSR, "/ead//control/filedesc/editionstmt/edition[@localtype='typology']", "ead3_strumenti", "tipologia_tipologia");
        $descrizioneEstrinseca_tipoSupporto = $this->xpath->query("//ead//control/filedesc/editionstmt/edition[@localtype='support']");
        $this->verifyVocabulary($descrizioneEstrinseca_tipoSupporto, "/ead//control/filedesc/editionstmt/edition[@localtype='support']", "ead3_strumenti", "descrizioneEstrinseca_tipoSupporto");
        $modalitaDiRedazione_edito = $this->xpath->query("//ead//control/filedesc/editionstmt/edition[@localtype='published']");
        $this->verifyVocabulary($modalitaDiRedazione_edito, "/ead//control/filedesc/editionstmt/edition[@localtype='published']", "ead3_strumenti", "modalitaDiRedazione_edito");
        $modalitaDiRedazione_tipologia = $this->xpath->query("//ead//control/filedesc/editionstmt/edition[@localtype='tipologia']");
        $this->verifyVocabulary($modalitaDiRedazione_tipologia, "/ead//control/filedesc/editionstmt/edition[@localtype='tipologia']", "ead3_strumenti", "modalitaDiRedazione_tipologia");
        $compilazione_azioneSR = $this->xpath->query("//ead//control/maintenancehistory/maintenanceevent/eventtype/@value");
        $this->verifyVocabulary($compilazione_azioneSR, "/ead//control/maintenancehistory/maintenanceevent/eventtype/@value", "ead3_strumenti", "compilazione_azione");
        $compilazione_tipologiaRedattoreSR = $this->xpath->query("//ead//control/maintenancehistory/maintenanceevent/agenttype/@value");
        $this->verifyVocabulary($compilazione_tipologiaRedattoreSR, "/ead//control/maintenancehistory/maintenanceevent/agenttype/@value", "ead3_strumenti", "compilazione_tipologiaRedattore");
        $validita = $this->xpath->query("//ead//control/filedesc/publicationstmt/date[@localtype='certainty']");
        $this->verifyVocabulary($validita, "/ead//control/filedesc/publicationstmt/date[@localtype='certainty']", "ead3", "validita");
    }

    function validateTracciatiSpecifici()
    {
        $cartografiaNode = $this->xpath->query("//ead//did/materialspec[@localtype='Scala'] | //ead//did/physdescstructured/physfacet[@localtype='tipoRappresentazione'] | //ead//did/unitid[@label='numeroTavola'] | //ead//did/physdescstructured/descriptivenote/p/geogname/part[@localtype='luogoRappresentato']");
        $this->verifyTracciatiSpecifici($cartografiaNode, ['cartografia']);
        $manoscrittoNode = $this->xpath->query("//ead//did/physdescstructured/descriptivenote/p/quote[@localtype='incipit'] | //ead//did/physdescstructured/descriptivenote/p/quote[@localtype='explicit']");
        $this->verifyTracciatiSpecifici($manoscrittoNode, ['manoscritto']);
        $graficaNode = $this->xpath->query("//ead//did/physdescstructured/physfacet[@localtype='materiaTecnica'] | //ead//did/physdescstructured/physfacet[@localtype='caratteristicheTecniche'] | //ead//did/physdescstructured/physfacet[@localtype='tipologia']");
        $this->verifyTracciatiSpecifici($graficaNode, ['grafica']);
        $audiovisivoNode = $this->xpath->query("//ead//did/physdescstructured/physfacet[@localtype='durata'] | //ead//did/physdescstructured/physfacet[@localtype='sonoro']");
        $this->verifyTracciatiSpecifici($audiovisivoNode, ["audiovisivo"]);
        $fotografiaNode = $this->xpath->query("//ead//did/unitdatestructured/datesingle[@localtype='dataRipresa']");
        $this->verifyTracciatiSpecifici($fotografiaNode, ["fotografia"]);
        $luogoRappresentatoNode = $this->xpath->query("//ead//controlaccess/geogname/part[@localtype='luogoRappresentato']");
        $this->verifyTracciatiSpecifici($luogoRappresentatoNode, ['grafica', 'fotografia']);
        $tecnicaNode = $this->xpath->query("//ead//did/physdescstructured/physfacet[@localtype='tecnica']");
        $this->verifyTracciatiSpecifici($tecnicaNode, ['cartografia', 'fotografia', 'audiovisivo']);
        $indicatoreColoreNode = $this->xpath->query("//ead//did/physdescstructured/physfacet[@localtype='BN/colore']");
        $this->verifyTracciatiSpecifici($indicatoreColoreNode, ['grafica', "fotografia", "audiovisivo"]);
    }

    function validateDAONumber()
    {
        $did = $this->xpath->query('//did');
        foreach ($did as $node) {
            $daoNodes = $this->xpath->query('./dao', $node);
            if ($daoNodes->length > 1) {
                $parent = $node->parentNode;
                $completePath = $parent->getNodePath();
                $path = substr($completePath, strpos($completePath, ':recordBody') + 11);
                $this->fileLogger->writeLogLine('WARNING', $path, "La scheda possiede più elementi dao non inseriti in un elemento daoset.", $parent);
            }
        }
    }

    function validateDate()
    {
        $attribute = ['normal', 'standarddate', 'standardDate', 'notafter', 'notAfter', 'notbefore', 'notBefore'];
        $specificaBeforeRange = ['01', '46', '90', '00', '51', '26', '76'];
        foreach ($attribute as $attr) {
            $nodeList = $this->xpath->query("//*/@$attr");
            foreach ($nodeList as $node) {
                $completePath = $node->getNodePath();
                $path = substr($completePath, strpos($completePath, ':recordBody') + 11);
                $value = $node->nodeValue;
                $originalVaue = $value;
                $value = str_replace(['-', '/'], '', $value);
                if (!is_numeric($value)) {
                    $this->fileLogger->writeLogLine('ERROR', $path, "Il valore $originalVaue di tipo data deve essere numerico.", $node);
                    $this->fatalError = true;
                }
                if ($attr == 'notbefore' || $attr == 'notBefore') {
                    $specificaPart = substr($value, 2, 2);
                    if (!in_array($specificaPart, $specificaBeforeRange)) {
                        $this->fileLogger->writeLogLine('WARNING', $path, "Il valore $originalVaue di tipo data secolare ha una specifica non conforme.", $node);
                    }
                }
            }
        }
    }

    // private function writeLogLine($type, $path, $msg, $node = null)
    // {
    //     if (is_null($node)) {
    //         $line = "";
    //     } else {
    //         $line = $node->getLineNo();
    //     }
    //     if ($this->firstLine) {
    //         error_log("Rigo documento\tXpath\tTipo\tMessaggio\n", 3, $this->warningFile . '.log');
    //         $this->firstLine = false;
    //     }
    //     error_log("$line\t$path\t$type\t$msg\n", 3, $this->warningFile . '.log');
    // }

    private function verifyDefault($node, $nodeName)
    {
        if (!$node->length || trim($node[0]->nodeValue) == '') {
            $this->fileLogger->writeLogLine('ERROR', $nodeName, "L'elemento non è mappato nel file di configurazione oppure ha un valore vuoto.");
            $this->fatalError = true;
        }
    }

    private function verifyVocabulary($node, $nodeName, $type, $field, $exceptions =  [])
    {
        foreach ($node as $n) {
            $value = strtolower($n->nodeValue);
            $query = "/root/$type/vocabulary/$field/entry[php:functionString('strtolower', @value)='$value']";
            $pathForLog = "/root/$type/vocabulary/$field/entry[@value='$value']";
            $res = $this->configXpath->query($query);
            if (!$res->length || trim($res[0]->nodeValue) === '') {
                if (!in_array($value, $exceptions)) {
                    $this->fileLogger->writeLogLine('ERROR', $nodeName, "Il valore $value dell'elemento non è mappato nel file di configurazione. L'elemento $pathForLog è assente o ha valore vuoto.", $n);
                    $this->fatalError = true;
                }
            }
        }
    }

    private function verifyTracciatiSpecifici($node, $type)
    {
        foreach ($node as $n) {
            $error = false;
            $value = trim($n->nodeValue);
            if ($value !== '') {
                $parentName = '';
                $parent = $n->parentNode;
                while ($parentName !== 'c' && $parentName !== 'archdesc') {
                    $parent = $parent->parentNode;
                    $parentName = $parent->tagName;
                }
                $tracciatoNode = $this->xpath->query("./controlaccess/genreform/part");
                if ($tracciatoNode->length) {
                    $tracciatoVal = strtolower($tracciatoNode[0]->nodeValue);
                    if (!in_array($tracciatoVal, $type)) {
                        $error = true;
                    }
                } else {
                    $error = true;
                }
                if ($error) {
                    $types = implode(',', $type);
                    $nodeName = '/' . substr($n->getNodePath(), strpos($n->getNodePath(), '/ead/'));
                    $this->fileLogger->writeLogLine('ERROR', $nodeName, "L'elemento descrive un'unità documentaria di tipo $types non conforme al tipo di scheda.", $n);
                    $this->fatalError = true;
                }
            }
        }
    }

    private function detectCurrentLevel($node)
    {
        if (!$node->hasAttribute('level')) {
            return false;
        }
        $value = $node->getAttribute('level');
        $levels = [
            'CA' => ['collection', 'fonds', 'recordgrp', 'subfonds', 'series', 'subseries'],
            "UA" => ['file'],
            "UD" => ['item']
        ];
        foreach ($levels as $key => $arr) {
            if (in_array($value, $arr)) {
                return $key;
            }
        }
        if (!$node->hasAttribute('otherlevel')) {
            $completePath = $node->getNodePath();
            $path = substr($completePath, strpos($completePath, ':recordBody') + 11);
            $this->fileLogger->writeLogLine('ERROR', $path, "La scheda non appartiene a nessuna gerarchia archivistica", $node);
            $this->fatalError = true;
            return '';
        }
        $otherLevel = $node->getAttribute('otherlevel');
        $vocabularyValue = '';
        $vocabularyNode = $this->configXpath->query("/root/ead3/vocabulary/livelloDescrizione/entry[php:functionString('strtolower', @value)='$otherLevel']");
        if ($vocabularyNode->length) {
            $vocabularyValue = $vocabularyNode[0]->nodeValue;
        }
        $otherLevels = [
            'CA' => ['complesso-di-fondi', 'superfondo', 'fondo', 'sub-fondo', 'sezione', 'serie', 'sottoserie', 'sottosottoserie', 'collezione-raccolta'],
            'UA' => ['unita', 'sottounita', 'sottosottounita'],
            'UD' => ['documento-principale', 'unita-documentaria', 'documento-allegato']
        ];
        foreach ($otherLevels as $key => $arr) {
            if (in_array($vocabularyValue, $arr)) {
                return $key;
            }
        }
        return '';
    }

    private function checkLevelCoherence($curLevel, $internalPath, $path, $node, $attribute = '')
    {
        if (!$curLevel) {
            return true;
        }
        if (!$attribute) {
            $type = explode('##', $internalPath)[1];
        } else {
            $type = explode('##', $attribute)[1];
        }
        if (!isset($type)) {
            return true;
        }
        $p = explode('##', $internalPath)[0];
        if ($p !== $path) {
            return true;
        }
        if ($curLevel[0] == 'U' && $type[0] == 'U') {
            return true;
        }
        if ($curLevel == $type) {
            return true;
        }
        if ($attribute) {
            $path .= '/@' . explode('##', $attribute)[0];
        }
        $schedaType = $this->returnType($curLevel);
        $this->fileLogger->writeLogLine('WARNING', $path, "L'elemento non descrive una scheda di tipo $schedaType di metaFAD", $node);
        return false;
    }

    private function returnType($type)
    {
        if ($type == 'CA') {
            return "Complesso archivistico";
        } elseif ($type == 'UA') {
            return "Unita archivistica";
        } elseif ($type = 'UD') {
            return "Unita documentaria";
        } else {
            return "Unita";
        }
    }

    private function renameLogFile()
    {
        $warningFile = $this->warningFile . '.log';
        $newName = str_replace('.log', '_logError.log', $warningFile);
        rename($warningFile, $newName);
    }
}
