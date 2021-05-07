<?php

class metafad_common_views_renderer_SelectionFlag extends pinax_components_render_RenderCell
{
	public function renderCell($key, $value, $row, $columnName)
	{
		$output = '<input type="checkbox" id="flag_' . $key . '" class="selectionflag" data-id="'.$key.'"/>';

		return $output;
	}
}
