<?php
class metafad_common_views_renderer_CellEditDraftDelete extends metafad_common_views_renderer_AbstractCellEdit
{
    public function renderCell($key, $value, $row, $columnName)
    {
        parent::renderCell($key, $value, $row, $columnName);

        $draft = ($row->hasPublishedVersion) ? $row->hasDraftVersion : true;

        $output = $this->renderEditButton($key, $row, $row->hasPublishedVersion).
                  $this->renderEditDraftButton($key, $row, $draft).
                  $this->renderDeleteButton($key, $row) ;

        return $output;
    }
}
