<?php
class metafad_mag_controllers_ajax_GetMagFromRecord extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($id)
    {
        $result = $this->checkPermissionForBackend('visible');
        if (is_array($result)) {
            return $result;
        }

      if(!$id)
      {
        return array();
      }
      $helper = pinax_ObjectFactory::createObject('metafad.mag.helpers.MappingHelper',
                  $this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy'));
      $record = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
      if($record->load($id))
      {
        $fields = array();
        $model = (strpos($record->document_type,".models.") === false) ? $record->document_type.'.models.Model' : $record->document_type;
        $mapping = $helper->getMapping($record->getRawData(),array(),$model,$record->document_id);
        if($mapping)
        {
          foreach ($mapping as $key => $value) {
            if(!is_string($value))
            {
              foreach ($value as $v) {
                $fields[$key][] = $v->{$key.'_value'};
              }
            }
            else {
              $fields[$key] = $value;
            }
          }
        }
        return $fields;
      }
      return null;
    }
}
