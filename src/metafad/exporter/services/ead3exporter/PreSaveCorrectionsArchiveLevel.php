<?php
set_time_limit(0);
ini_set('memory_limit', '2048M');

class metafad_exporter_services_ead3exporter_PreSaveCorrectionsArchiveLevel extends PinaxObject
{
    private $docWrite;

    public function __construct($docWrite)
    {
        $this->docWrite = $docWrite;
    }

    public function doCorrections()
    {
        $this->validatePhysDescStructured();
        $this->validateDaoSet();
        $this->addUnderscoresToId();
        return $this->docWrite;
    }

    private function validatePhysDescStructured()
    {
        $nodes = $this->docWrite->getElementsByTagName('physdescstructured');
        if ($nodes->length === 0) {
            return;
        }
        foreach ($nodes as $node) {
            $quantity = false;
            $unittype = false;
            $nodeBefore = null;
            foreach ($node->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    if ($child->nodeName === 'quantity') {
                        $quantity = true;
                    } elseif ($child->nodeName === 'unittype') {
                        $unittype = true;
                        $nodeBefore = $child;
                        if (!$quantity) {
                            break;
                        }
                    } else {
                        $nodeBefore = $child;
                        break;
                    }
                }
            }
            if (!$quantity && !$unittype) {
                $element = $this->docWrite->createElement('unittype');
                $nodeBefore = $node->insertBefore($element, $nodeBefore);
                $element2 = $this->docWrite->createElement('quantity');
                $node->insertBefore($element2, $nodeBefore);
            } elseif (!$quantity && $unittype) {
                $element = $this->docWrite->createElement('quantity');
                $node->insertBefore($element, $nodeBefore);
            } elseif ($quantity && !$unittype) {
                $element = $this->docWrite->createElement('unittype');
                $node->insertBefore($element, $nodeBefore);
            }
        }
    }

    private function validateDaoSet()
    {
        $nodes = $this->docWrite->getElementsByTagName('daoset');
        if ($nodes->length === 0) {
            return;
        }
        for ($i = 0; $i < $nodes->length; ++$i) {
            $children = $nodes[$i]->getElementsByTagName('dao');
            if ($children->length === 1) {
                $nodeToInsert = $children[0];
                $nodes[$i]->parentNode->appendChild($nodeToInsert);
                $nodes[$i]->parentNode->removeChild($nodes[$i]);
                --$i;
            }
            if ($this->docWrite->getElementsByTagName('daoset')->length === 0)
                break;
        }
    }

    private function addUnderscoresToId() {
        $recordIdNode = $this->docWrite->getElementsByTagName('recordid');
        foreach($recordIdNode as $node) {
            $recordId = $node->nodeValue;
            $recordId = str_replace(' ', '_', $recordId);
            $node->nodeValue = $recordId;
        }

        $idNode = $this->docWrite->getElementsByTagName('unitid');
        foreach($idNode as $node) {
            if($node->getAttribute('localtype') == 'metaFAD') {
                $id = $node->getAttribute('identifier');
                $id = str_replace(' ', '_', $id);
                $node->setAttribute('identifier', $id);
                $node->nodeValue = $id;
            }
        }
    }
}
