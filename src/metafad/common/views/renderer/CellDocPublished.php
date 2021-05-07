<?php
class metafad_common_views_renderer_CellDocPublished extends pinax_components_render_RenderCell
{
	public function renderCell($key, $value, $row, $columnName)
	{
		if ($value==='PUBLISHED') $value = '<span class="'.__Config::get('pinax.datagrid.checkbox.on').'"></span>';
		else $value = '<span class="'.__Config::get('pinax.datagrid.checkbox.off').'"></span>';
		return $value;
	}
}
