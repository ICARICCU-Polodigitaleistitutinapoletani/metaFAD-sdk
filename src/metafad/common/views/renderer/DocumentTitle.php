<?php
class metafad_common_views_renderer_DocumentTitle extends pinax_components_render_RenderCell
{
    public function renderCell( $key, $value, $row, $columnName )
    {
        return $value;
    }
}