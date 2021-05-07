<?php

class metafad_gestioneDati_schedeSemplificate_views_renderer_CellForm extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
    {
		  return $value->text;
	  }
}
