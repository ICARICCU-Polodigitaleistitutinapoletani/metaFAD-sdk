<?php

class metafad_mets_controllers_media_ajax_Save extends metafad_common_controllers_ajax_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
      $result = $this->checkPermissionAndInstitute('publish', $data);
      if (is_array($result)) {
          return $result;
      }

      $conditionHelper = pinax_ObjectFactory::createObject('metafad_mag_helpers_ConditionHelper');
      $decodeData = json_decode($data);
      $modelNameSplit = explode(".",$decodeData->__model);
      $type = lcfirst(end($modelNameSplit));
      $errors = $conditionHelper->checkMediaCondition($decodeData,$type);
      if(empty($errors))
      {
        return true;
      }
      else {
        $this->directOutput = true;
        return array('errors' => $errors);
      }
    }
}
