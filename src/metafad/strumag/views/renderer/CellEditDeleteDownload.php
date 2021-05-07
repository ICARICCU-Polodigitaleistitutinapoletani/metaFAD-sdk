<?php

class metafad_strumag_views_renderer_CellEditDeleteDownload extends pinaxcms_contents_views_renderer_AbstractCellEdit
{

    protected function renderDownloadButton($key, $row)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output .= __Link::makeLinkWithIcon('actionsMVC',
                __Config::get('pinax.datagrid.action.downloadCssClass'),
                array(
                    'title' => 'Download',
                    'id' => $key,
                    'model' => $row->getClassName(false),
                    'action' => 'download'
                ));
        }

        return $output;
    }



    public function renderCell($key, $value, $row, $columnName)
    {
        $output = $this->renderEditButton($key, $row) .
                $this->renderDeleteButton($key, $row);
        return $output;
    }
}

