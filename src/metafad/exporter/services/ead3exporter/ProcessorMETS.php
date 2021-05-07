<?php

class metafad_exporter_services_ead3exporter_ProcessorMETS extends metafad_exporter_services_ead3exporter_BaseProcessor
{
    private $metsMapping;
    private $metsRec;
    private $onlyHref;

    public function __construct($dirRead, $docWrite, $domConfig, $instituteKey)
    {
        parent::__construct($docWrite, $domConfig, $instituteKey);
        $this->metsMapping = json_decode(file_get_contents($dirRead . "maps/METS.json"));
        $this->defaultMapping = json_decode(file_get_contents($dirRead . "maps/defaultMETS.json"));
        $this->defineBaseXpath('/mets:mets');
        $this->defineNamespace('mets', 'http://www.loc.gov/METS/');
        $this->defineConfigDOMElement('mets');
        $this->metsRec = new DOMDocument();
        $this->metsRec->load($dirRead . 'metsRec.xml');
    }

    public function processRoots($id)
    {
        $this->context = null;
        $this->processDefault();
        //$this->setMetsIds($ids[0]);
        $entity = pinax_ObjectFactory::createModel('archivi.models.Model');
        $entity->setType(null);
        if ($entity->load($id, 'PUBLISHED_DRAFT')) {
            foreach ($this->metsMapping as $field => $properties) {
                $field = str_replace('#', '', $field);
                if (!is_object($properties)) {
                    $this->processField($field, $properties, $entity);
                } else {
                    $this->processObjectField($field, $properties, $entity);
                }
            }
            $this->fillImgTags($entity);
        }
        return $this->docWrite;
    }

    private function fillImgTags($entity)
    {
        $identificativo = str_replace(' ', '_', $entity->identificativo);
        try {
            $metadataLink = $entity->linkedStruMag;
            $ar = pinax_ObjectFactory::createModel("metafad.strumag.models.Model");
            $ar->load($metadataLink->id);
            $images = json_decode($ar->physicalSTRU);
        } catch (Exception $e) {
            $images = new StdClass();
            $image[] = json_decode($entity->mediaCollegati);
            $images->image = $image;
        }
        $i = 1;
        foreach ($images->image as $img) {
            $type = str_replace('\'', '', pathinfo($img->file_name, PATHINFO_EXTENSION));
            $niso = $this->retrieveNiso($img, $type);
            $this->fillTechNode($niso, $type, $identificativo, $i, $img->id);
            $this->fillFileSecNode($type, $identificativo, $i, $img->id);
            $this->fillStructMapNode($identificativo, $i, $img->id, $img->title);
            ++$i;
            $this->docWrite->saveXML();
        }
    }

    private function fillTechNode($niso, $type, $identificativo, $i, $imgId)
    {
        $info = $niso->NisoImg;
        $metsRecCloned = clone $this->metsRec;
        $techNode = $metsRecCloned->getElementsByTagName('techMD')[0];
        if (!$this->onlyHref) {
            $techNode->setAttribute('ID', "TD_$identificativo" . "_IMG000$i");
        } else {
            $techNode->setAttribute('ID', "TD_$identificativo" . "_$imgId");
        }
        $compressionScheme = $metsRecCloned->getElementsByTagName('compressionScheme')[0];
        $compressionScheme->nodeValue = $type;
        $imageWidthNode = $metsRecCloned->getElementsByTagName('imageWidth')[0];
        $imageWidthNode->nodeValue = $info->image_width;
        $imageHeightNode = $metsRecCloned->getElementsByTagName('imageHeight')[0];
        $imageHeightNode->nodeValue = $info->image_length;
        $bitsPerSampleValue = $metsRecCloned->getElementsByTagName('bitsPerSampleValue')[0];
        $bitsPerSampleValue->nodeValue = $info->bit_per_sample;
        $colorSpace = $metsRecCloned->getElementsByTagName('colorSpace')[0];
        $colorSpace->nodeValue = null;
        $imported = $this->docWrite->importNode($techNode, true);
        $rightsNodes = $this->xpath->query("/mets:mets/mets:amdSec/mets:rightsMD");
        if ($rightsNodes->length) {
            $rightsNode = $rightsNodes[0];
            $rightsNode->parentNode->insertBefore($imported, $rightsNode);
        } else {
            $amdNode = $this->xpath->query("/mets:mets/mets:amdSec")[0];
            $amdNode->appendChild($imported);
        }
    }

    private function fillFileSecNode($type, $identificativo, $i, $imgId)
    {
        $file = $this->metsRec->getElementsByTagName('file')[0];
        $file->setAttribute('MIMETYPE', "image/$type");
        if (!$this->onlyHref) {
            $file->setAttribute('ID', "$identificativo" . "_IMG000$i");
        } else {
            $file->setAttribute('ID', "$identificativo" . "_$imgId");
        }
        if (!$this->onlyHref) {
            $file->setAttribute('ADMID', "TD_$identificativo" . "_IMG000$i MD_$identificativo");
        } else {
            $file->setAttribute('ADMID', "TD_$identificativo" . "_$imgId MD_$identificativo");
        }
        $fLocat = $this->metsRec->getElementsByTagName('FLocat')[0];
        if (!$this->onlyHref) {
            $fLocat->setAttribute('xlink:href', "$identificativo/IMG000$i.$type");
        } else {
            //TODO l'url sarà da cambiare
            $url = $this->dam->streamUrlLocal($imgId, 'original');
            $fLocat->setAttribute('xlink:href', $url);
        }
        $imported = $this->docWrite->importNode($file, true);
        $fileGrpNode = $this->xpath->query("/mets:mets/mets:fileSec/mets:fileGrp")[0];
        $fileGrpNode->appendChild($imported);
    }

    private function fillStructMapNode($identificativo, $i, $imgId, $title)
    {
        $div = $this->metsRec->getElementsByTagName('div')[0];
        $div->setAttribute('LABEL', $title);
        $fptr = $div->getElementsByTagName('fptr')[0];
        if (!$this->onlyHref) {
            $fptr->setAttribute('FILEID', "$identificativo" . "_IMG000$i");
        } else {
            $fptr->setAttribute('FILEID', "$identificativo" . "_$imgId");
        }
        $imported = $this->docWrite->importNode($div, true);
        $structMapNode = $this->xpath->query("/mets:mets/mets:structMap/mets:div")[0];
        $structMapNode->appendChild($imported);
    }

    private function retrieveNiso($img, $type)
    {
        $url = $this->dam->mediaUrl($img->id) . '?bytestream=true';
        $bytestream = json_decode(file_get_contents($url));
        foreach ($bytestream->bytestream as $b) {
            if ($b->name == 'original') {
                $original = $b;
                break;
            }
        }
        if ($original) {
            $nisoUrl = $this->dam->mediaUrl($img->id) . '/bytestream/' . $original->id . '/datastream/NisoImg';
            $niso = json_decode(file_get_contents($nisoUrl));
        }
        $content = file_get_contents($this->dam->streamUrlLocal($img->id, 'original'));
        $niso->filesize = mb_strlen($content, '8bit');
        $niso->md5 = md5($content);
        $mediaId = $img->id;
        $str = file_get_contents($this->dam->streamUrlLocal($mediaId, 'original'));
        $exif = exif_read_data("data://image/$type;base64," . base64_encode($str), 'FILE');
        //se serve md5
        //$md5Sum = md5($str);
        return $niso;
    }

    public function setOnlyHref($type)
    {
        if ($type != 'file') {
            $this->onlyHref = true;
        } else {
            $this->onlyHref = false;
        }
    }
}
