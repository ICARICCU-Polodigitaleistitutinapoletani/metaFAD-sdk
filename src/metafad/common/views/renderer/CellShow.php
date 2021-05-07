<?php
class metafad_common_views_renderer_CellShow extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    protected function renderShowButton($key, $row, $enabled = true)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon(
                'actionsMVC',
                __Config::get('pinax.datagrid.action.showOnlyCssClass').($enabled ? '' : ' disabled'),
                array(
                    'title' => 'Visualizza',
                    'id' => $key,
                    'action' => 'edit',
                    'cssClass' => ($enabled ? '' : ' disabled-button')
                )
            );
        }

        return $output;
    }

    public function renderCell($key, $value, $row, $columnName)
    {
        parent::renderCell($key, $value, $row, $columnName);

        $output = $this->renderShowButton($key, $row, true);

        return $output;
    }
}
