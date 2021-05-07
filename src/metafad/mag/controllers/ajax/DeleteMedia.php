<?php
class metafad_mag_controllers_ajax_DeleteMedia extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($id,$option)
    {
        $result = $this->checkPermissionForBackend('delete');
        if (is_array($result)) {
            return $result;
        }
        
      //Estraggo informazioni su docstru
      $docStruProxy = $this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy');
      $rootId = $docStruProxy->getRootNodeByDocumentId($id);

      //Cancello da documents
      if($option == 0 || $option == 2)
      {
        $docstruId = $rootId->docstru_id;
        $it = pinax_ObjectFactory::createModelIterator('metafad.mag.models.Publication')
              ->where('docstru_rootId',$docstruId);
        foreach ($it as $ar) {
          $ar->delete();
        }
        //Cancello da docStru
        $it = pinax_ObjectFactory::createModelIterator('metafad.mag.models.Docstru')
              ->where('docstru_rootId',$docstruId)
              ->where('docstru_parentId',$docstruId);
        foreach ($it as $ar) {
          $ar->delete();
        }
      }
      return array('sendOutput' => 'fileTabs', 'sendOutputState' => 'edit', 'sendOutputFormat' => 'html');
    }
}
