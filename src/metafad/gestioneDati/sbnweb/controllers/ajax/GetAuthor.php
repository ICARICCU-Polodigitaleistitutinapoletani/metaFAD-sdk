<?php
class metafad_gestioneDati_sbnweb_controllers_ajax_GetAuthor extends metafad_common_controllers_Command
{
    public function execute($formModel,$vid,$n)
    {
      $formVersion = preg_replace('/[^0-9]/','',$formModel);
      $model = 'AUT'.$formVersion.'.models.Model';
      $ar = pinax_ObjectFactory::createModelIterator($model)
            ->where('VID',$vid)->first();

      $flowsFile = pinax_findClassPath("metafad/gestioneDati/sbnweb/config/config.json", false, false);
      $jsonConfig = file_get_contents($flowsFile);
      $jsonConfig = json_decode($jsonConfig);
      
      $fields = array();
      foreach($jsonConfig as $c)
      {
        $key = key($c);
        $fields[$key] = $c->$key;
      }

      $fields = $fields[$formVersion];

      if($ar)
      {
        $valuesToImport = array();
        foreach ($ar->getValuesAsArray() as $key => $value) {
          if(strpos($key,'AU') !== false)
          {
            $valuesToImport[$key] = $value;
          }
        }

        $field0 = $fields[0];
        $field1 = $fields[1];
        $valuesToImport['__AUT'] = array(
            'id' => $ar->document_id,
            'text' => $ar->$field0.' - '.$ar->$field1
        );
      }
      else
      {
        $valuesToImport = false;
      }

      return array('values'=>$valuesToImport,'n'=>$n);
    }
}
