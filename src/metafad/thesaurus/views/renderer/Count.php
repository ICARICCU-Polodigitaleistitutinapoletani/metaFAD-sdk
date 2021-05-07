<?php

class metafad_thesaurus_views_renderer_Count extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
	{
		if(__Config::get('metafad.thesaurus.filterInstitute'))
		{
			$it = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.Details');
			$it->load('instituteFilter', array('params' => array('thesaurusId' => $value, 'institute_key' => metafad_usersAndPermissions_Common::getInstituteKey())));
			return $it->count();
		}
		else
		{
			$it = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.Details')
				->where('thesaurusdetails_FK_thesaurus_id',$value);
			return $it->count();
		}
	}
}
