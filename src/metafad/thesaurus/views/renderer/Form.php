<?php

class metafad_thesaurus_views_renderer_Form extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
	{
	    return $row->forms;
	}
}
