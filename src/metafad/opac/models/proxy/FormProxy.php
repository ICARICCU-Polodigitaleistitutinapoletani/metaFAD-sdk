<?php

class metafad_opac_models_proxy_FormProxy extends PinaxObject
{
  public function findTerm($fieldName, $model, $query, $term, $proxyParams)
  {
    $modules = pinax_Modules::getModules();
    $modelsList = array();
    foreach ($modules as $key => $value) {
      if($value->isICCDModule && !$value->isAuthority)
      {
        $modelsList[] = array(
          'id' => $value->iccdModuleType,
          'text' => $value->classPath,
        );
      }
    }
    return $modelsList;
  }
}
