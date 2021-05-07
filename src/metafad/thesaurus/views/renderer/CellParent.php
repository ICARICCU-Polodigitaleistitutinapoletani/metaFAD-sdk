<?php
class metafad_thesaurus_views_renderer_CellParent extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
	{
	    $document = pinax_ObjectFactory::createModel('metafad.thesaurus.models.ThesaurusDetails');

	    $it = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusDetails');
        $it->where('thesaurusdetails_id', $value);

        foreach ($it as $ar) {
					//TODO Valutare poi se mostrare key o value
            $parent = $ar->getRawData()->thesaurusdetails_key;
        }

        $value = '<div class="thesaurusParent" id="thesaurusParent-'.$row->thesaurusdetails_id.'" onchange="saveParent($(this));" data-key="'.$row->thesaurusdetails_id.'" data-val="' . $parent . '"></div>';
				return $value;
	}
}
