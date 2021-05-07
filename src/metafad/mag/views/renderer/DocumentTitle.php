<?php
class metafad_mag_views_renderer_DocumentTitle extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
    {
      return $value[0]->BIB_dc_title_value;
    }
}
