<?php

abstract class metafad_exporter_services_ead3exporter_BaseProcessor extends PinaxObject
{
    protected $defaultMapping;
    protected $docWrite;
    protected $domConfig;
    protected $xpath;
    protected $baseXpath;
    protected $namespacePrefix;
    protected $namespaceURI;
    protected $context;
    protected $domXpath;
    protected $logger;
    protected $defaultIndex;
    protected $dam;
    protected $metadataBlocks;
    protected $configDOMElement;
    protected $recordsToprocess;

    public function __construct($docWrite, $domConfig, $instituteKey)
    {
        $this->docWrite = $docWrite;
        $this->xpath = new DOMXPath($docWrite);
        $this->domConfig = $domConfig;
        $this->domXpath = new DOMXPath($domConfig);
        $this->domXpath->registerNamespace("php", "http://php.net/xpath");
        $this->domXpath->registerPHPFunctions('strtolower');
        $this->dam = pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia', $instituteKey);
        $this->logger = pinax_ObjectFactory::createObject("metafad_exporter_services_ead3exporter_Logger");
        $this->recordsToprocess = [];
    }

    public abstract function processRoots($id);

    public function defineBaseXpath($baseXpath)
    {
        $this->baseXpath = $baseXpath;
    }

    public function defineNamespace($prefix, $uri)
    {
        $this->namespacePrefix = $prefix;
        $this->namespaceURI = $uri;
        $this->xpath->registerNamespace($prefix, $uri);
    }

    public function defineConfigDOMElement($element)
    {
        $this->configDOMElement = $element;
    }

    public function processDefault()
    {
        foreach ($this->defaultMapping as $field => $properties) {
            $this->defaultIndex = 0;
            if (!is_object($properties)) {
                $this->processField($field, $properties, new StdClass());
            } else {
                $nodeDefaultTags = $this->domXpath->query('/root/' . $this->configDOMElement . '/mapping/' . $properties->defaultRoot);
                for ($i = 0; $i < $nodeDefaultTags->length; ++$i) {
                    $this->defaultIndex = $i;
                    $this->processObjectField($field, $properties, new StdClass());
                }
            }
        }
    }

    public function processField($field, $properties, $entity)
    {
        foreach ($properties as $block) {
            if ($block->skipIfNodeNotExists) {
                if (!$this->xpath->query($block->nodeToCheck, $this->context)->length) {
                    continue;
                }
            }
            $resField = $entity->$field;
            if ($block->simpleJoin) {
                $resField = $this->simpleJoin($resField, $block->fieldsToJoin, $entity);
            }
            if (!is_object($resField)) {
                $resField = trim($resField);
            }
            if (!$resField && !$block->hasDefault) {
                continue;
            }
            $resField = $this->extractFieldValue($block, $resField, $entity->identificativo, $entity->document_id);
            if (!$resField) {
                continue;
            }
            $node = $this->xpath->query($block->xpath, $this->context);
            if ($node->length == 0) {
                $this->buildNodes($block->xpath, $block->notCreateNewNodeIfExists, false, null, null, $block->appendToFirst, $block->insertBefore, $block->xpathToBuildNode, $block->limitExplode);
                if ($this->context) {
                    $this->context = $this->xpath->query("//ead:c[./ead:did/ead:unitid/text()='{$entity->identificativo}']")[0];
                }
                $node = $this->xpath->query($block->xpath, $this->context);
            }
            if (!$block->attribute) {
                $node[0]->nodeValue = $resField;
            } else {
                $node[0]->setAttribute($block->attribute, $resField);
            }
        }
    }

    public function processObjectField($field, $properties, $entity)
    {
        $resFieldArray = $entity->$field;
        if ($properties->cronologia) {
            if (!$properties->cronologiaMultiple && (count($resFieldArray) > 1 || $resFieldArray[0]->notaDatazione)) {
                return;
            }
            if ($properties->cronologiaMultiple && count($resFieldArray) < 2 && !$resFieldArray[0]->notaDatazione) {
                return;
            }
        }
        if ($properties->onlyFirstRep) {
            $resFieldArray = $this->getOnlyFirstRep($resFieldArray);
        }
        if ($properties->forceObject && $resFieldArray) {
            if (!is_string($resFieldArray)) {
                $resFieldArray = (object) $resFieldArray;
            }
            $ob = new StdClass();
            $ob->$field = $resFieldArray;
            unset($resFieldArray);
            $resFieldArray[] = $ob;
        }
        $xpath = $properties->xpath;
        $manageDefault = $properties->hasDefault;
        if ($manageDefault && (count($resFieldArray) === 0 || !$resFieldArray)) {
            $resFieldArray[] = '';
        }
        foreach ($resFieldArray as $arr) {
            if ($properties->purgeIfEmpty) {
                $resField = $arr->{$properties->purgeField};
                if (!$resField) {
                    continue;
                }
            }
            if ($properties->cronologia || $properties->cronologiaPurge) {
                if ($this->purgeCronologia($arr)) {
                    continue;
                }
            }
            $this->buildObjectBaseNode($xpath, $properties->objectRoot, $manageDefault, $entity->identificativo, $properties->notCreateNewNodeIfExists, $properties->insertBefore, $properties->preserveNS);
            $manageDefault = false;
            foreach ($properties->object as $block) {
                if ($block->field === '##cronologia##' || $block->field === '##redattore##' || $block->field === '##denominazione##' || $block->field === '.') {
                    $resField = $arr;
                } else {
                    $resField = $arr->{$block->field};
                }
                if (!is_object($resField) && !is_array($resField)) {
                    $resField = trim($resField);
                }
                if ($block->selectiveBuild) {
                    if (!$this->selectiveBuild($arr, $block->selectiveBuild)) {
                        continue;
                    }
                }
                if ($resField || $block->default || $block->hasDefault || $block->constant || $block->extractor) {
                    $resFieldCopy = $resField;
                    $resField = $this->extractFieldValue($block, $resField, $entity->identificativo, $entity->document_id);
                    if (!$resField) {
                        if ($block->removeEmptyNode) {
                            $this->removeNode($xpath);
                        }
                        continue;
                    }
                    if (!$properties->isMetadata) {
                        $this->setObjectFieldValue($xpath, $block, $resFieldCopy, $resField, $entity, $properties);
                    } else {
                        $this->manageMetadata($resField, $properties->xpath, $entity);
                    }
                }
            }
        }
    }

    public function setObjectFieldValue($xpath, $block, $resFieldCopy, $resField, $entity, $properties)
    {
        if ($block->buildMoreObjectNodes && is_array($resField)) {
            $resFirst = array_shift($resField);
            $resArray = $resField;
            $resField = $resFirst;
        }
        $completePath = $this->buildObjectSubNodes($xpath, $block, $resFieldCopy, $resField);
        if ($this->context) {
            $this->context = $this->xpath->query("//ead:c[./ead:did/ead:unitid/text()='{$entity->identificativo}']")[0];
            $nodeList = $this->xpath->query($completePath, $this->context);
        } else {
            $nodeList = $this->xpath->query("/$completePath");
        }
        $node = $nodeList[$nodeList->length - 1];
        if (!$block->attribute) {
            $node->nodeValue = $resField;
        } else {
            if ($block->setIdentificativoSede) {
                $resField = $resField == 1 ? $resField : $nodeList->length;
            }
            $node->setAttribute($block->attribute, $resField);
        }
        if (isset($resArray)) {
            foreach ($resArray as $r) {
                $this->buildObjectBaseNode($xpath, $properties->objectRoot, false, $entity->identificativo, false);
                $this->setObjectFieldValue($xpath, $block, $resFieldCopy, $r, $entity, $properties);
            }
        }
    }


    public function removeNode($xpath)
    {
        $nodeList = $this->xpath->query($xpath, $this->context);
        if ($nodeList->length) {
            $node = $nodeList[$nodeList->length - 1];
            $node->parentNode->removeChild($node);
            $this->updateDOM();
        }
    }

    public function selectiveBuild($fatherOb, $params)
    {
        $field = $params->field;
        $value = $params->value;
        if ($fatherOb->$field !== $value) {
            return false;
        }
        return true;
    }

    public function getOnlyFirstRep($resFieldArray)
    {
        if (count($resFieldArray) > 1) {
            $first = $resFieldArray[0];
            $resFieldArray = [];
            $resFieldArray[] = $first;
        }
        return $resFieldArray;
    }

    public function manageMetadata($res, $xpath, $entity)
    {
        $resField = '';
        foreach ($res as $k => $v) {
            foreach ($this->metadataBlocks as $block) {
                switch ($block->attribute) {
                    case 'identifier':
                        $resField = $k;
                        break;
                    case 'daotype':
                        $resField = 'derived';
                        break;
                    case 'coverage':
                        $resField = 'whole';
                        break;
                    case 'href':
                        $resField = $v[0];
                        break;
                    case 'linkrole':
                        $resField = $v[1];
                        break;
                    case 'label':
                        $resField = $v[2];
                        break;
                }
                if ($block->attribute !== 'identifier') {
                    $block->xpath = "./ead:dao[@identifier='$k']";
                }
                $completePath = $this->buildObjectSubNodes($xpath, $block, $resField);
                $this->context = $this->xpath->query("//ead:c[./ead:did/ead:unitid/text()='{$entity->identificativo}']")[0];
                $nodeList = $this->xpath->query($completePath, $this->context);
                $node = $nodeList[$nodeList->length - 1];
                if (!$block->attribute) {
                    $node->nodeValue = $resField;
                } else {
                    $node->setAttribute($block->attribute, $resField);
                }
            }
        }
    }

    public function extractFieldValue($block, $resField, $id, $docId)
    {
        if ($block->utility) {
            $resField = pinax_ObjectFactory::createObject('metafad_exporter_services_ead3exporter_utilities_UtilitiesEAD3')->{$block->utility}($resField, $block->utilityParams);
        }
        if ($block->constant) {
            $resField = $block->constant;
        }
        if ($block->extractor) {
            if (!$resField) {
                return false;
            }
            $resField = pinax_ObjectFactory::createObject('metafad_exporter_services_ead3exporter_utilities_Extractor')->{$block->extractor[0]}($resField, $block->extractorParams);
        }
        if ($block->mediaExtractor) {
            if ($block->metadata) {
                // TODO ELIMINARE QUESTO IF
                //$this->idsImages[str_replace(' ', '_', $id)] = $resField->id;
            } elseif ($block->singleImg) {
                //$this->idsImages[str_replace(' ', '_', $id)] = $resField;
            }
            $resField = pinax_ObjectFactory::createObject('metafad_exporter_services_ead3exporter_utilities_MediaExtractor', $this->dam)->{$block->mediaExtractor}($resField, $block->mediaExtractorAttr);
        }
        if ($block->joiner) {
            $resField = pinax_ObjectFactory::createObject('metafad_exporter_services_ead3exporter_utilities_Joiner')->{$block->joiner}($resField, $docId);
        }
        if ($resField && $block->dictionary) {
            $attr = strtolower($resField);
            $domXpath = '/root/' . $this->configDOMElement . '/vocabulary/' . $block->dictionary . "/entry[php:functionString('strtolower', @value)='$attr']/text()";
            $resField = $this->domXpath->query($domXpath)[0]->nodeValue;
            if (!$resField) {
                $this->logger->write(0, $id, "Il valore '$attr' della lista " . "'" . $block->dictionary . "'" . " non è stato esportato perché non è stato definito nel file di configurazione.");
            }
            if ($resField == '#non mappato#') {
                $resField = '';
            }
        }

        if (!$resField) {
            $defElements = explode('#', $block->default);
            $pathDefault = trim($defElements[0] . '/' . $defElements[1], '/');
            $resField = $this->domXpath->query('/root/' . $this->configDOMElement . '/mapping/' . $pathDefault)[$this->defaultIndex]->nodeValue;
        }

        if (!$resField && $block->default) {
            $this->logger->write(1, $id, "Il valore di default del campo " . "'" . $block->default . "'" . " non è stato definito nel file di configurazione.");
        }

        if ($resField && $block->addToProcessRecord) {
            $this->addRecordToProcess($resField, $block->recordParams);
        }
        return $resField;
    }

    public function buildNodes($xpath, $notCreateNewNode = false, $addNodeObject = false, $objectBaseXpath = null, $newNodeDeep = null, $appendTofirst = false, $insertBefore = false, $xpathToBuildNode = false, $limitExplode = null, $preserveNS = false)
    {
        if ($addNodeObject) {
            $baseXpath = $objectBaseXpath;
        } elseif (!$this->context) {
            $baseXpath = $this->baseXpath;
        } else {
            $baseXpath = '.';
        }
        $baseNodeList = $this->xpath->query($baseXpath, $this->context);
        if ($addNodeObject) {
            $baseNode = $baseNodeList[$baseNodeList->length - 1];
        } else {
            $baseNode = $baseNodeList[0];
        }
        if (!$xpathToBuildNode) {
            $string = str_replace($baseXpath, '', $xpath);
        } else {
            $string = str_replace($baseXpath, '', $xpathToBuildNode);
        }
        if (is_null($limitExplode)) {
            $nodes = explode('/', substr($string, 1));
        } else {
            $nodes = explode('/', substr($string, 1), $limitExplode);
        }
        $updateDOM = false;
        $deep = 0;
        foreach ($nodes as $node) {
            if (!$node) {
                continue;
            }
            $nodeComplete = $node;
            $nodeComplete = str_replace('##', '/', $nodeComplete);
            $attributeString = '';
            if (strpos($node, '@') !== false) {
                list($node, $attributeString) = $this->manageAttributeString($node);
            }
            $baseXpath .= '/' . $nodeComplete;
            $foundedNodes = $this->xpath->query($baseXpath, $this->context);
            //if ($foundedNodes->length === 1 && $notCreateNewNode && $deep === 0)
            if ($foundedNodes->length !== 0 && $notCreateNewNode && $deep === 0 && is_null($newNodeDeep)) {
                $addNodeObject = false;
                //elseif ($foundedNodes->length > 1 || $deep > 0)
            } elseif (($foundedNodes->length > 0 || $deep > 0) && !is_null($newNodeDeep)) {
                $addNodeObject = false;
                ++$deep;
                if ($deep >= $newNodeDeep && !is_null($newNodeDeep)) {
                    $addNodeObject = true;
                }
            }
            if ($updateDOM || $addNodeObject || $foundedNodes->length == 0) {
                $updateDOM = true;
                if (!$preserveNS) {
                    $node = str_replace($this->namespacePrefix . ':', '', $node);
                }
                $element = $this->docWrite->createElement($node);
                foreach ($attributeString as $attr) {
                    $this->appendAttribute($element, $attr);
                }
                if (!$insertBefore) {
                    $baseNode = $baseNode->appendChild($element);
                } else {
                    if (!$this->checkIfNodesExist($baseNode, $insertBefore, $element)) {
                        $baseNode = $baseNode->appendChild($element);
                    }
                }
            } else {
                if (!$appendTofirst) {
                    $baseNode = $foundedNodes[$foundedNodes->length - 1];
                } else {
                    $baseNode = $foundedNodes[0];
                }
            }
        }
        if ($updateDOM) {
            $this->updateDOM();
        }
    }

    public function checkIfNodesExist($domElement, $nodes, $element)
    {
        foreach ($nodes as $node) {
            $elements = $domElement->getElementsByTagName($node);
            if ($elements->length == 0) {
                continue;
            }
            $position = $elements[$elements->length - 1];
            $position->parentNode->insertBefore($element, $position);
            return true;
        }
        return false;
    }

    public function buildObjectBaseNode($xpath, $objectRoot, $manageDefault, $id, $notCreateNode, $insertBefore = false, $preserveNS = false)
    {
        $baseXpath = substr($xpath, 0, strpos($xpath, $objectRoot) - 1);
        $baseNodeList = $this->xpath->query($baseXpath, $this->context);
        $baseNode = $baseNodeList[0];
        if ($baseNodeList->length === 0) {
            $this->buildNodes($baseXpath);
            $baseNodeList = $this->xpath->query($baseXpath, $this->context);
            $baseNode = $baseNodeList[0];
        }
        $string = str_replace($baseXpath, '', $xpath);
        $nodes = explode('/', substr($string, 1));
        foreach ($nodes as $node) {
            $attributeString = '';
            if (strpos($node, '@') !== false) {
                list($node, $attributeString) = $this->manageAttributeString($node);
            }
            $baseXpath .= '/' . $node;
            $foundedNodes = $this->xpath->query($baseXpath, $this->context);
            if ($foundedNodes->length !== 0 && $notCreateNode) {
                return;
            }
            if (!$preserveNS) {
                $node = str_replace($this->namespacePrefix . ':', '', $node);
            }
            $element = $this->docWrite->createElement($node);
            foreach ($attributeString as $attr) {
                $this->appendAttribute($element, $attr);
            }
            if (!$insertBefore) {
                $baseNode = $baseNode->appendChild($element);
            } else {
                if (!$this->checkIfNodesExist($baseNode, $insertBefore, $element)) {
                    $baseNode = $baseNode->appendChild($element);
                }
            }
        }
        $this->updateDOM();
        if ($this->context) {
            $this->context = $this->context = $this->xpath->query("//ead:c[./ead:did/ead:unitid/text()='$id']")[0];
        }
    }

    public function buildObjectSubNodes($xpath, $block, $resField, $resFieldOriginal = '')
    {
        if (!$resField && $block->buildDefaultPath) {
            $resField = $resFieldOriginal;
        }
        $xpathCopy = $block->xpath;
        if ($block->extractor && strpos($block->xpath, '##placeholder##') !== false) {
            if (!$resField) {
                return false;
            }
            $xpathToReplace = pinax_ObjectFactory::createObject('metafad_exporter_services_ead3exporter_utilities_Extractor')->{$block->extractor[1]}($resField);
            if ($block->pathRebuilder) {
                $xpathToReplace = pinax_ObjectFactory::createObject('metafad_exporter_services_ead3exporter_utilities_PathRebuilder')->{$block->pathRebuilder}($xpathToReplace, $block->isQualifica);
            }
            $block->xpath = str_replace('##placeholder##', $xpathToReplace, $block->xpath);
        }
        $completePath = $xpath . substr($block->xpath, 1);
        $this->buildNodes($completePath, $block->notCreateNewNodeIfExists, true, $xpath, $block->newNodeDeep, false, false, false, $block->limitExplode, $block->preserveNS);
        $block->xpath = $xpathCopy;
        return $completePath;
    }

    public function manageAttributeString($nodeString)
    {
        $string = explode('[', $nodeString);
        for ($i = 1; $i < count($string); ++$i) {
            $attr = rtrim($string[$i], ']');
            $attr = str_replace('##', '/', $attr);
            $attributes[] = $attr;
        }
        return [$string[0], $attributes];
    }

    public function appendAttribute($element, $attributeString)
    {
        if ($attributeString == '' || strpos($attributeString, '!=') || strpos($attributeString, 'not(') !== false) {
            return;
        }
        $attributeString = str_replace([' ', "'", '@'], '', $attributeString);
        $strings = explode('=', $attributeString);
        $element->setAttribute($strings[0], $strings[1]);
    }

    public function purgeCronologia($cronologia)
    {
        if (
            !$cronologia->estremoRemoto_data
            && !$cronologia->estremoRemoto_secolo
            && !$cronologia->estremoRecente_data
            && !$cronologia->estremoRecente_secolo
        ) {
            return true;
        }
        return false;
    }

    public function simpleJoin($res, $fields, $entity)
    {
        if (!is_string($res)) {
            return $res;
        }
        if (!$res) {
            $res = '';
        }
        $res = trim($res, '.');
        foreach ($fields as $field) {
            if (is_string($entity->$field) && $entity->$field) {
                $res .= ". " . trim($entity->$field, '.');
            }
        }
        if ($res !== '') {
            return ltrim($res, '. ') . '.';
        }
        return '';
    }

    public function generateMetadataBlocks()
    {
        $attr = ['identifier', 'daotype', 'coverage', 'href', 'linkrole', 'label'];
        for ($i = 0; $i < count($attr); ++$i) {
            $block = new StdClass();
            $block->xpath = './ead:dao';
            $block->attribute = $attr[$i];
            if ($attr[$i] != 'identifier') {
                $block->notCreateNewNodeIfExists = true;
            }
            $this->metadataBlocks[] = $block;
        }
    }

    public function updateDOM()
    {
        $xml = $this->docWrite->saveXML();
        $this->docWrite = new DOMDocument();
        $this->docWrite->loadXML($xml);
        $this->xpath = new DOMXPath($this->docWrite);
        $this->xpath->registerNamespace($this->namespacePrefix, $this->namespaceURI);
        $this->xpath->registerNamespace("php", "http://php.net/xpath");
        $this->xpath->registerPHPFunctions('strtolower');
        unset($xml);
    }

    public function addRecordToProcess($identificativo, $params)
    {
        $strings = explode('_', $identificativo);
        $arr = ['id' => $strings[2], 'processorType' => $params->processorType, 'templateType' => $params->templateType];
        $this->recordsToprocess[] = $arr;
    }

    public function getRecordToProcess()
    {
        return $this->recordsToprocess;
    }
}
