<?php
class metafad_strumag_controllers_Ecommerce extends pinaxcms_contents_controllers_moduleEdit_Edit
{
  function execute($id)
  {
    $c = $this->view->getComponentById('strumagSection');
    $c->setAttribute('ecommerce',true);
    parent::execute($id);
  }
}
