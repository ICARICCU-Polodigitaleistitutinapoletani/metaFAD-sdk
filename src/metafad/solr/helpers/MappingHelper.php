<?php
class metafad_solr_helpers_MappingHelper extends PinaxObject
{
  use metafad_solr_helpers_HelpersTrait;
  private $lastElementField;

  public function translateLabel($label, $array, $otherTranslation = null)
  {
    if (array_key_exists($label, $array)) {
      return $array[$label];
    } else if (array_key_exists($label, $otherTranslation)) {
      return $otherTranslation[$label];
    } else {
      return __T($label);
    }
  }
}
