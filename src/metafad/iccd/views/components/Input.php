<?php
class metafad_iccd_views_components_Input extends pinax_components_Input
{
  function init()
  {
      $this->defineAttribute('defaultValue',  false, 	'',	COMPONENT_TYPE_STRING);
      $this->defineAttribute('parentId',  false, 	null,	COMPONENT_TYPE_STRING);
      parent::init();
  }

  //TODO POLODEBUG-380,237
  public function render($outputMode=NULL, $skipChilds=false)
  {
    $output = '<div class="form-group">';
    $output .= '<label for="'.$this->getAttribute('id').'" class="col-sm-2 control-label required">'.$this->getAttribute('label').'</label>';
    $output .= '<div class="col-sm-9"><select id="'.$this->getAttribute('id').'" name="'.$this->getAttribute('id').'" class="form-control">';
    $siteMap = pinax_ObjectFactory::createObject('pinax.application.SiteMapXML');

    $pages = array();
    $parentId = explode(',', $this->getAttribute('parentId'));
    $exclude = array ("tei-unitacodicologica","tei-unitatestuale","gestione-dati/authority/iccd","mods");

    foreach ($siteMap->getSiteArray() as $key => $value) {
        if (!in_array($value['id'],$exclude) && (!$parentId || in_array($value['parentId'], $parentId))) {
            $pages[$value['id']] = $value['title'];
        }
    }
    asort($pages);
    foreach ($pages as $key => $value)
    {
      if($value != '')
      {
        if($key == $this->getAttribute('defaultValue')){
          $selected = 'selected="selected"';
        }
        else {
          $selected = '';
        }
        $output .= '<option '.$selected.' value="'.$key.'">'.$value.'</option>';
      }
    }
    $output .= '</select></div></div>';
    $this->addOutputCode($output);
  }

}
