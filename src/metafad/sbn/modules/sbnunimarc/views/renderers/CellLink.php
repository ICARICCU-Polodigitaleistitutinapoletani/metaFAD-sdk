<?php

class metafad_sbn_modules_sbnunimarc_views_renderers_CellLink extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    public function renderCell($key, $value, $row, $columnName)
    {
        $output = '<a href="' . __Link::makeURL( 'actionsMVC',
                array(
                    'title' => __T('PNX_RECORD_EDIT'),
                    'action' => 'show','id' => $row->id)) . '" target="_blank">' . $row->id . '</a>';
        return $output;
    }
}
