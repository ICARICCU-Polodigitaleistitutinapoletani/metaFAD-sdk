<?php
class metafad_gestioneDati_boards_models_proxy_ArchiveToIccdProxy extends PinaxObject
{
	public function findTerm($fieldName, $model, $query, $term, $proxyParams)
	{
		$result = array();

		if($proxyParams)
		{
			foreach ($proxyParams as $key => $value) {
				$it = pinax_ObjectFactory::createModelIterator($value)
						->where('instituteKey',metafad_usersAndPermissions_Common::getInstituteKey());

				foreach ($it as $ar) {
					if(!$ar->parent && $ar->_denominazione)
					{
						$result[] = array(
							'id' => $ar->getId(),
							'text' => $ar->_denominazione,
							'model' => $value
						);
					}
				}
			}
		}

		return $result;
	}
}
