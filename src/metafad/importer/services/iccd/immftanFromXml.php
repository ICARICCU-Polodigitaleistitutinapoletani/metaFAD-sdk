<?php
class metafad_importer_services_iccd_ImmftanFromXml extends metafad_importer_services_iccd_Immftan
{
    private $values;

    public function __construct($file)
    {
        $this->read($file);
    }

    private function read($file)
    {
        $this->values = array();

        if (!file_exists($file))
            $file = $this->tryOtherNames($file);

        if (!file_exists($file))
            return false;

        $xmlDoc = new DOMDocument();
        $xmlDoc->load($file);
        $xpath = new DOMXpath($xmlDoc);
        $elements = $xpath->query("/csm_immftan/csm_def/relazione");
        $this->readXmlIccd($elements);

        return true;
    }


    private function readXmlIccd($elements){
      foreach($elements as $element) {
        if($element->getElementsByTagName('file')->item(0)->nodeValue && $element->getElementsByTagName('identificativo_allegato')->item(0)->getElementsByTagName('nome')->item(0)->nodeValue=="FTAN"){
          $this->values[$element->getElementsByTagName('identificativo_allegato')->item(0)->getElementsByTagName('valore')->item(0)->nodeValue]=$element->getElementsByTagName('file')->item(0)->nodeValue;
        }
      }
    }


    public function getImages($record)
    {
        $images = array();

        if (property_exists($record, 'FTA')) {
            foreach ($record->FTA as $fta) {
                $images[] = $this->values[trim($fta->FTAN)];
            }
        }

        return $images;
    }


    public function count()
    {
        return count($this->values);
    }
}
