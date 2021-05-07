<?php
class metafad_thesaurus_views_renderer_CellLevel extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
	{
			$output = '';
			for($i=1;$i<=5;$i++)
			{
				$class = ($value == $i) ? 'level selected':'level' ;
				$output .= '<span class="'.$class.'"><input type="button" style="border:none; padding: 0px; background:none; box-shadow: none;" data-id="'.$row->thesaurusdetails_id.'" value="'.$i.'" onClick="clickLevelAndSave($(this));"></input></span>';
			}
		return $output;
	}
}
