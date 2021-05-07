<?php

class metafad_usersAndPermissions_users_views_renderer_CellEditDeleteDetail extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    protected function renderDetailButton($key, $row)
    {
        $output = '';
        if ($this->canView && $this->canDelete) {
            $output .= __Link::makeLinkWithIcon('actionsMVC',
                __Config::get('pinax.datagrid.action.detailCssClass'),
                array(
                    'title' => 'Dettaglio',
                    'id' => $key,
                    'action' => 'detail'
                ));
        }
        return $output;
    }

    public function renderCell($key, $value, $row, $columnName)
    {
        $this->loadAcl($key);
        $output = $this->renderEditButton($key, $row) .
            $this->renderDeleteButton($key, $row) .
            $this->renderDetailButton($key, $row);
        return $output;
    }
}

