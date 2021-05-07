<?php
class metafad_thesaurus_views_renderer_CellValue extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
	{
		$value = '<input id="iccd_theasaurus_value" data-type="value" data-id="'.$row->thesaurusdetails_id.'" name="iccd_theasaurus_value" onchange="editDataGrid($(this));" class="form-control thesaurus_value" type="text" value="' . $value .'">';
		return $value;
	}
}
