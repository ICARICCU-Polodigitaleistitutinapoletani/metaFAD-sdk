<?php
class metafad_mag_controllers_ajax_GetElement extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($start,$stop,$type,$idParent)
    {
        $result = $this->checkPermissionForBackend('visible');
        if (is_array($result)) {
            return $result;
        }
        
      $docStruProxy = $this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy');
      $parentId = $docStruProxy->getRootNodeByDocumentId($idParent)->getId();
      $it = pinax_ObjectFactory::createModelIterator('metafad.mag.models.'.ucfirst($type))
            ->where('docstru_type',$type)
            ->where('docstru_parentId',$parentId)
            ->where('sequence_number',$start,'>=')
            ->where('sequence_number',$stop,'<=');
      $output = '';
      if($type == 'img')
      {
        foreach ($it as $ar) {
          $output .= '<img class="element-media" src="application/templates/images/noimage.jpg" alt="'.$ar->sequence_number.'" title="'.json_decode($ar->document_detail_object)->title.'"/>';
        }
      }
      else
      {
        $output .= '<ul>';
        foreach ($it as $ar) {
          $output .= '<li>'.json_decode($ar->document_detail_object)->title.'</li>';
        }
        $output .= '</ul>';
      }
      return $output;
    }
}
