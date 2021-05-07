<?php
class metafad_gestioneDati_boards_views_renderer_InverseRelations extends pinax_components_render_RenderCellRecordSetList
{
    public function renderCell(&$ar, $params)
    {
      $relatedId = $ar->relation_FK_document_id;
      $ar->relation_type = __T(__T($ar->relation_type));
      $relatedForm = $ar->relation_this_form;
      $tsk = pinax_Modules::getModule($relatedForm)->iccdModuleType;
      $relatedForm = ($relatedForm == null) ? 'Non specificato' : $relatedForm;
      $ar->tsk = ($tsk) ?: $relatedForm;
      $ar->title = $ar->relation_this_title;
      if($relatedId != 0)
      {
        $ar->link = __Link::makeUrl('linkEdit',array('pageId'=>$relatedForm,'id'=>$relatedId));
      }
    }
}
