<?php
class metafad_viewer_helpers_ViewerHelper extends PinaxObject
{
  public function getKey($check)
  {
    return ($check)?:'*';
  }

  public function initializeDam($key){
    if($key) {
      return pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia', $key);
    }
    else {
      return pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia', '*');
    }
  }
}
