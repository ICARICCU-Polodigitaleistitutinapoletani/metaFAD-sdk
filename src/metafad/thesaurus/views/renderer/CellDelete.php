<?php
class metafad_thesaurus_views_renderer_CellDelete extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
	{
	    return '<a href="#" class="js-delete-row" data-id="'.$row->thesaurusdetails_id.'" title="Cancella"><i class="btn btn-danger btn-flat fa fa-trash"></i> </a>';
	}
}
