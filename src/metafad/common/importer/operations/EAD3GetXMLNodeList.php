<?php
class metafad_common_importer_operations_EAD3GetXMLNodeList extends metafad_common_importer_operations_EADGetXMLNodeList
{
    protected $fileLogger;

    function __construct(stdClass $params, metafad_common_importer_MainRunner $runnerRef)
    {
        $warningFile = "./export/icar-import_log_folder/" . md5($params->logFile);
        $this->fileLogger = pinax_ObjectFactory::createObject('metafad_common_importer_utilities_ImportFileLogger', $warningFile);
        parent::__construct($params, $runnerRef);
    }

    function execute($input)
    {
        $ret = new stdClass();
        $ret->argset = array();

        foreach ($input->document as $archdesc) {
            $arr = $this->getAllSubNodesBFS($archdesc, $this->rootXPath, $this->childXPath, $this->idXPath, $this->recordId);

            //$ret->argset = array_map(function($a){$returned = new stdClass(); $returned->domElement = $a; return $returned;}, $arr['nodes']);
            foreach ($arr['nodes'] as $k => $v) {
                $ret->argset[] = (object)array("domElement" => $v, "idDomElement" => $k);
                if ($this->dumpDirectory) {
                    $this->dumpNode($this->dumpDirectory, $k, $v);
                }
            }
        }

        return $ret;
    }
    protected function getAllSubNodesBFS($xDoc, $rootXPath, $childXPath, $idXPath, $recordId = null)
    {
        $retNodes = array();
        $parentTable = array();
        $xPath = new DOMXPath($xDoc);
        $fifo = array();
        //gestire eventualmente Id root per complessi root multipli (l'id del archdesc deve coincidere con quello di control (ma inutile questo controllo se il blocco control viene tolto))
        foreach ($xPath->query($rootXPath) as $node) {
            $fifo[] = $node;
            $controlsNode = $xPath->query('/ead/control');
            $node->appendChild($controlsNode[0]);
            //TODO Settare qui l'id del complesso a cui agganciarsi
            if (!is_null($this->recordId)) {
                $node->setAttribute("metafad_parentId", "rootCA##$recordId");
            }
        }
        $father = null;
        while (count($fifo) > 0) {
            $node = array_shift($fifo);
            $idNodes = $xPath->query($idXPath, $node);
            $idNode = $this->idNodeManage($idNodes, $node);
            $father = $idNode;
            foreach ($xPath->query($childXPath, $node) as $child) {
                $fifo[] = $child;
                $childIdNodes = $xPath->query($idXPath, $child);
                $childId = $this->idNodeManage($childIdNodes, $child);
                $child->setAttribute("metafad_parentExternalId", $father);
                $parentTable[$childId] = $father;
            }

            $retNodes[$idNode] = $node;
            $this->editHierarchyLevel($node, $idNode, $retNodes, $parentTable);
            $node->setAttribute("metafad_acronimoSistema", $this->systemAcronym ?: "UKWN");
            $this->getSubChildes($node, $father, $parentTable, $retNodes, $xPath, $idXPath);
        }
        unset($fifo, $xPath);
        return array("nodes" => $retNodes, "parentTable" => $parentTable);
    }

    protected function getSubChildes($child, $childId, &$parentTable, &$retNodes, $xPath, $idXPath)
    {
        $fifo = array();
        foreach ($xPath->query('./c', $child) as $subChild) {
            $fifo[] = $subChild;
        }
        while (count($fifo) > 0) {
            $node = array_shift($fifo);
            $idNodes = $xPath->query($idXPath, $node);
            $idNode = $this->idNodeManage($idNodes, $node);
            $node->setAttribute("metafad_parentExternalId", $childId);
            $parentTable[$idNode] = $childId;
            $retNodes[$idNode] = $node;
            $this->editHierarchyLevel($node, $idNode, $retNodes, $parentTable);
            $node->setAttribute("metafad_acronimoSistema", $this->systemAcronym ?: "UKWN");
            if ($xPath->query('./c', $node)->length > 0) {
                $this->getSubChildes($node, $idNode, $parentTable, $retNodes, $xPath, $idXPath);
            }
        }
    }

    protected function idNodeManage($idNodes, $node)
    {
        if ($idNodes->length !== 0) {
            $idNode = $idNodes[0]->textContent;
        } else {
            $idNode = uniqid(mt_rand(), true);
            $node->setAttribute("added_id", $idNode);
        }
        return $idNode;
    }

    protected function editHierarchyLevel($node, $idNode, $retNodes, $parentTable)
    {
        $descLvlToNumber = $this->getDescrLevels();
        $numToDescLvl = $this->invertArray($descLvlToNumber);
        $curLevel = $node->getAttribute("level");
        $curLevel = $node->getAttribute("otherlevel") ?: $curLevel;

        switch ($curLevel) {
            case 'item':
                $parent = $retNodes[$parentTable[$idNode]];
                if ($parent) {
                    $parLvlDescr = $this->getParentLevel($parentTable, $retNodes, $idNode, $descLvlToNumber);
                    $lvl = $descLvlToNumber['documento-principale'];
                } else {
                    $lvl = $descLvlToNumber['documento-principale'];
                }
                break;
            case 'sottofascicolo':
                $parent = $retNodes[$parentTable[$idNode]];
                if ($parent) {
                    $parLvlDescr = $this->getParentLevel($parentTable, $retNodes, $idNode, $descLvlToNumber);
                    $lvl = ($parLvlDescr === 10 || $parLvlDescr === 11) ? $descLvlToNumber['sottosottounita'] : $descLvlToNumber['sottounita'];
                } else {
                    $lvl = $descLvlToNumber['sottounita'];
                }
                break;
            case 'file':
                $parent = $retNodes[$parentTable[$idNode]];
                if ($parent) {
                    $parLvlDescr = $this->getParentLevel($parentTable, $retNodes, $idNode, $descLvlToNumber);
                    $node = $retNodes[$idNode];
                    switch ($parLvlDescr) {
                        case 9:
                            $lvl = $descLvlToNumber['sottounita'];
                            $this->fileLogger->writeLogLine('WARNING', '/ead//c/@level', "La scheda di livello file è stata trasformata in sottounita", $node);
                            break;
                        case 10:
                            $lvl = $descLvlToNumber['sottosottounita'];
                            $this->fileLogger->writeLogLine('WARNING', '/ead//c/@level', "La scheda di livello file è stata trasformata in sottosottounita", $node);
                            break;
                        case 11:
                            $lvl = $descLvlToNumber['sottosottounita'];
                            $this->fileLogger->writeLogLine('WARNING', '/ead//c/@level', "La scheda di livello file è stata trasformata in sottosottounita", $node);
                            break;
                        default:
                            $lvl = $descLvlToNumber['unita'];
                    }
                } else {
                    $lvl = $descLvlToNumber['unita'];
                }
                break;
            case 'subseries':
                $parent = $retNodes[$parentTable[$idNode]];
                if ($parent) {
                    $parLvlDescr = $this->getParentLevel($parentTable, $retNodes, $idNode, $descLvlToNumber);
                    $lvl = ($parLvlDescr === 6 || $parLvlDescr === 7) ? $descLvlToNumber['sottosottoserie'] : $descLvlToNumber['sottoserie'];
                } else {
                    $lvl = $descLvlToNumber['sottoserie'];
                }
                break;
            case 'series':
                $lvl = $descLvlToNumber['serie'];
                break;
            case 'subfonds':
                $lvl = $descLvlToNumber['sub-fondo'];
                break;
            case 'recordgrp':
                $lvl = $descLvlToNumber['complesso-di-fondi'];
                break;
            case 'fonds':
                $lvl = $descLvlToNumber['fondo'];
                break;
            case 'collection':
                $lvl = $descLvlToNumber['collezione-raccolta'];
                break;
            case 'otherlevel':
                $lvl = $descLvlToNumber['fondo'];
                break;
            default:
                $val = $this->configXpath->query("/root/ead3/vocabulary/livelloDescrizione/entry[php:functionString('strtolower', @value)='$curLevel']")[0]->nodeValue;
                $lvl = $descLvlToNumber[$val];
        }
        $node->setAttribute('metafad_livelloDiDescrizione', $numToDescLvl[$lvl]);
        $node->setAttribute('metafad_model', $this->inferModelFromLivelloDescrizione($lvl));
    }

    protected function getParentLevel($parentTable, $retNodes, $idNode, $descLvlToNumber)
    {
        $parent = $retNodes[$parentTable[$idNode]];
        if ($parent) {
            $parLvlDescr = $parent->getAttribute('metafad_livelloDiDescrizione');
            $parentLvl = $parLvlDescr ? $descLvlToNumber[$parLvlDescr] : null;
            if (is_null($parentLvl)) {
                throw new Exception("C'è stato un errore nell'importazione: il livello di un nodo non può essere null");
            }
            return $parentLvl;
        }
    }

    protected function addControlInfo($node, $xPath)
    {
        $controlsNode = $xPath->query('/ead/control');
        foreach ($controlsNode as $cn) {
            $recordId = $cn->getElementsByTagName('recordid');
        }
    }

    function validateInput($input)
    {
    }
}
