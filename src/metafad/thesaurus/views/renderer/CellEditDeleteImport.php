<?php
class metafad_thesaurus_views_renderer_CellEditDeleteImport extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    public function renderCell($key, $value, $row, $columnName)
    {
        $this->loadAcl($key);
        $output = $this->renderEditButton($key, $row).
                    $this->renderDeleteButton($key, $row);
        return $output;
    }
}


