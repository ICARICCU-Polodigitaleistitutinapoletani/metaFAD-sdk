<?php
class metafad_mag_controllers_ajax_CheckMag extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($id)
    {
        $result = $this->checkPermissionForBackend('visible');
        if (is_array($result)) {
            return $result;
        }

      $it = pinax_ObjectFactory::createModelIterator('metafad.mag.models.Relations')
              ->where('mag_relation_stru_id',$id)
              ->where('mag_relation_parent',0)
              ->first();
      if($it)
      {
        $parent = pinax_ObjectFactory::createModel('metafad.mag.models.Model');
        $parent->load($it->mag_relation_FK_document_id, 'PUBLISHED_DRAFT');
        return $parent->BIB_dc_identifier_index;

      }
      else
      {
        return null;
      }
    }
}
