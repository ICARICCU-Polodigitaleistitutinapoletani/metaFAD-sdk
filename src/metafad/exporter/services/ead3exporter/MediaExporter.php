<?php
class metafad_exporter_services_ead3exporter_MediaExporter extends PinaxObject
{
    private $idsImages;
    private $doc;
    private $xpath;
    private $dirWrite;
    private $dam;
    private $format;
    private $jobId;
    private $job;
    private $updateProgress;

    public function __construct($idsImages, $doc, $dirWrite, $format, $jobId)
    {
        $this->idsImages = $idsImages;
        $this->doc = $doc;
        $this->xpath = new DOMXPath($doc);
        $this->xpath->registerNamespace('ead', 'http://ead3.archivists.org/schema/');
        $this->dirWrite = $dirWrite;
        $this->format = $format;
        $this->dam = pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia', metafad_usersAndPermissions_Common::getInstituteKey());
        $this->jobId = $jobId;
        $this->job = pinax_ObjectFactory::createModel('metafad.jobmanager.models.Job');
        if($this->job->load($this->jobId)) {
            $this->updateProgress = true;
        }
    }

    public function export()
    {
        $progress = 0;
        $progressStep = $this->calculateProgressStep(count($this->idsImages));
        foreach ($this->idsImages as $key => $val) {
            $count = 1;
            $maybeObject = pinax_maybeJsonDecode($val, false);
            if (is_object($maybeObject)) {
                $img = $maybeObject;
                $this->processImg($img, $key, $count);
                if ($this->format == 'mets') {
                    $this->addMetsRef($key);
                }
                continue;
            }
            $metadata = pinax_ObjectFactory::createModel("metafad.strumag.models.Model");
            $metadata->load($val);
            $images = json_decode($metadata->physicalSTRU);
            foreach ($images->image as $img) {
                $this->processImg($img, $key, $count);
                ++$count;
            }
            if ($this->format == 'mets') {
                $this->addMetsRef($key);
            }
            if($this->updateProgress) {
                $progress += $progressStep;
                $this->updateJob($progress);
            }
        }
        return $this->doc;
    }

    private function processImg($img, $key, $count)
    {
        $mediaId = $img->id;
        $href = "$key/IMG000$count";
        $ext = str_replace('\'', '', pathinfo($img->file_name, PATHINFO_EXTENSION));
        if ($this->format == 'dao') {
            $this->adjustDocHref($key, $href, $count, $ext);
        }
        $baseNameImg = "IMG000$count.$ext";
        $this->exportImgFiles($key, $baseNameImg, $mediaId);
    }

    private function adjustDocHref($key, $href, $count, $ext)
    {
        $node = $this->xpath->query("(//ead:did[./ead:unitid[@identifier='$key']]/ead:daoset/ead:dao)[$count]");
        if(!$node->length) {
            $key2 = str_replace('_', ' ', $key);
            $node = $this->xpath->query("(//ead:did[./ead:unitid[@identifier='$key2']]/ead:daoset/ead:dao)[$count]");
        }
        if (!$node->length) {
            $node = $this->xpath->query("//ead:did[./ead:unitid[@identifier='$key']]/ead:dao");
        }
        if(!$node->length) {
            $key2 = str_replace('_', ' ', $key);
            $node = $this->xpath->query("//ead:did[./ead:unitid[@identifier='$key2']]/ead:dao");
        }
        if ($node->length) {
            $node[0]->setAttribute('href', "$href.$ext");
        }
    }

    private function addMetsRef($key)
    {
        $node = $this->xpath->query("//ead:did[./ead:unitid[@identifier='$key']]");
        $dao = $this->doc->createElement('ead:dao');
        if ($node->length) {
            $dao->setAttribute('daotype', 'otherdaotype');
            $dao->setAttribute('otherdaotype', 'METS');
            $dao->setAttribute('coverage', 'whole');
            $dao->setAttribute('linkrole', 'text/xml');
            $dao->setAttribute('href', "$key.xml");
            $node[0]->appendChild($dao);
        }
    }

    private function exportImgFiles($key, $baseNameImg, $mediaId)
    {
        if (!file_exists($this->dirWrite . "/$key")) {
            mkdir($this->dirWrite . "/$key");
        }
        file_put_contents($this->dirWrite . "/$key/$baseNameImg", file_get_contents($this->dam->streamUrlLocal($mediaId, 'original')));
    }

    public function metsLinker() {
        foreach($this->idsImages as $key => $val) {
            $this->addMetsRef($key);
        }
        return $this->doc;
    }

    public function calculateProgressStep($records) {
        return (floor(100/$records) - 1);
    }

    public function updateJob($progress) {
        $this->job->job_progress = $progress;
        $this->job->job_modificationDate = new pinax_types_DateTime();
        $this->job->save();
    }
}
