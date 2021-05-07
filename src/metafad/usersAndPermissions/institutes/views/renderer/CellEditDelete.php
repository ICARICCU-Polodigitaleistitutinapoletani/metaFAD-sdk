<?php
class metafad_usersAndPermissions_institutes_views_renderer_CellEditDelete extends pinaxcms_contents_views_renderer_CellEditDelete
{
    public function renderCell($key, $value, $row, $columnName)
    {
        if ($row->institute_key != '*') {
            $this->loadAcl($key);
            $output = $this->renderEditButton($key, $row).
                      $this->renderDeleteButton($key, $row);
            return $output;
        }
    }
}