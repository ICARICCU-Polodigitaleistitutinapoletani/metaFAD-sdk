<?php
class metafad_common_views_renderer_CellEditDeleteSimple extends metafad_common_views_renderer_CellEditDraftDelete
{
  public function renderCell($key, $value, $row, $columnName)
  {
    parent::renderCell($key, $value, $row, $columnName);

    $output = $this->renderEditButton($key, $row, $row->hasPublishedVersion).
    $this->renderDeleteButton($key, $row) ;

    return $output;
  }
}
