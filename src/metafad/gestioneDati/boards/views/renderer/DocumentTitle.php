<?php
class metafad_gestioneDati_boards_views_renderer_Subject extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
    {
        if ($columnName == 'SGTT_s') {
            return $row->SGTT_s ? $row->SGTT_s : $row->OGTD_t;
        }
        return $value;
    }
}