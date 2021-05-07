<?php
set_time_limit(0);
ini_set('memory_limit', '2048M');

class metafad_exporter_services_ead3exporter_PreSaveCorrectionsProduttori extends PinaxObject
{
    private $docWrite;

    public function __construct($docWrite)
    {
        $this->docWrite = $docWrite;
    }

    public function doCorrections()
    {
        $this->validateExistDatesProduttori();
        return $this->docWrite;
    }

    private function validateExistDatesProduttori() {
        $nodes = $this->docWrite->getElementsByTagName('existDates');
        if ($nodes->length === 0) {
            return;
        } 
        $node = $nodes[0];
        $date = $node->getElementsByTagName('date');
        $dateRange = $node->getElementsByTagName('dateRange');
        if($date->length === 0 && $dateRange->length === 0) {
            $node->parentNode->removeChild($node);
        }
    }
}
