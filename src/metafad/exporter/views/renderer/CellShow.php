<?php
class metafad_exporter_views_renderer_CellShow extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    protected function renderShowButton($key, $row, $enabled = true)
    {
        $classNameSplit = explode('.', $row->className);
        $pageId = array_shift($classNameSplit);
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon(
                'actionsMVC',
                __Config::get('pinax.datagrid.action.showOnlyCssClass').($enabled ? '' : ' disabled'),
                array(
                    'title' => 'Visualizza',
                    'id' => $key,
                    'pageId' => $pageId,
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
