<?php
class metafad_common_views_renderer_CellEditDelete extends metafad_common_views_renderer_CellEditDraftDelete
{
  public function renderCell($key, $value, $row, $columnName)
  {
    parent::renderCell($key, $value, $row, $columnName);
    $draft = ($row->hasPublishedVersion) ? $row->hasDraftVersion : true;

    $output = $this->renderEditButton($key, $row, $row->hasPublishedVersion).
    $this->renderDeleteButton($key, $row) ;

    return $output;
  }
}
