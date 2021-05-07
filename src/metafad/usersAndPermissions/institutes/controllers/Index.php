<?php
class metafad_usersAndPermissions_institutes_controllers_Index extends pinaxcms_contents_controllers_activeRecordEdit_Edit
{
	public function execute($id)
	{
		$this->checkPermissionForBackend('visible');
		if(!__Config::get('metafad.be.hasInstitutes'))
		{
			$institute = pinax_ObjectFactory::createModelIterator('metafad.usersAndPermissions.institutes.models.Model');
			if($institute->count() > 0)
			{
				$instituteKey = $institute->first()->institute_key;
				metafad_usersAndPermissions_Common::setInstituteKey($instituteKey);
				$this->changePage('linkHome');
			}
		}

	}
}
