<?php
class metafad_common_views_renderer_CellEditDeleteLicenses extends metafad_common_views_renderer_AbstractCellEdit
{
  public function renderCell($key, $value, $row, $columnName)
  {
    parent::renderCell($key, $value, $row, $columnName);

    $output = $this->renderEditButton($key, $row, true).
    $this->renderDeleteSimpleButton($key, $row,'metafad.ecommerce.licenses.models.Model') ;

    return $output;
  }
}
