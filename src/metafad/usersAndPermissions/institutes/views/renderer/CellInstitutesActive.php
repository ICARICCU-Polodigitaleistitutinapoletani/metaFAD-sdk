<?php
class metafad_usersAndPermissions_institutes_views_renderer_CellInstitutesActive extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
	{
		$class = $value ? __Config::get('pinax.datagrid.checkbox.on') : __Config::get('pinax.datagrid.checkbox.off');
		$output = '<span class="'.$class.'"></span>';
		return $output;
	}
}
