<?php

class metafad_mag_controllers_media_ajax_Save extends pinaxcms_contents_controllers_moduleEdit_ajax_Save
{
    public function execute($data)
    {
      $conditionHelper = pinax_ObjectFactory::createObject('metafad_mag_helpers_ConditionHelper');
      $decodeData = json_decode($data);
      $modelNameSplit = explode(".",$decodeData->__model);
      $type = lcfirst(end($modelNameSplit));
      $errors = $conditionHelper->checkMediaCondition($decodeData,$type);
      if(empty($errors))
      {
        return parent::execute($data);
      }
      else {
        $this->directOutput = true;
        return array('errors' => $errors);
      }
    }
}
