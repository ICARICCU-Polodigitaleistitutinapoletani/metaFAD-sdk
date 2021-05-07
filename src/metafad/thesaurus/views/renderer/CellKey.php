<?php
class metafad_thesaurus_views_renderer_CellKey extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
	{
		$value = '<input id="iccd_theasaurus_key" data-type="key" data-id="'.$row->thesaurusdetails_id.'" name="iccd_theasaurus_key" onchange="editDataGrid($(this));" class="form-control" type="text" value="' . $value .'">';
		return $value;
	}
}
