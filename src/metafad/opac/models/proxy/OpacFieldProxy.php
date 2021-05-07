<?php

class metafad_opac_models_proxy_OpacFieldProxy extends PinaxObject
{
	public function findTerm($fieldName, $model, $query, $term, $proxyParams)
	{
		$section = $proxyParams->section;
		$form = $proxyParams->form;
		$archive = $proxyParams->archive;
		//Lista dei servizi per la get dei campi
		if($section == 'bibliografico')
		{
			$fieldsUrl = __Config::get('metafad.opac.fields.url');
			$sbn = true;
		}
		else if($section == 'metaindice')
		{
			$fieldsUrl = __Config::get('metafad.opac.metaindice.fields.url');
			$sbn = true;
		}
		else if($section == 'patrimonio')
		{
			$fields = json_decode(__Config::get('metafad.iccd.solr.commonFields'));
		}
		else if($section == 'archivi')
		{
			$fields = json_decode(file_get_contents( __Paths::get('APPLICATION_TO_ADMIN') . 'classes/metafad/solr/json/mappingArchive.json'));
			if($archive == 'ca')
			{
				$fields = $this->getArchiveFields($fields->{'archivi.models.ComplessoArchivistico'});
			}
			else {
				$fieldsUd = $this->getArchiveFields($fields->{'archivi.models.UnitaDocumentaria'});
				$fieldsUa = $this->getArchiveFields($fields->{'archivi.models.UnitaArchivistica'});
				$fields = array_merge($fieldsUd, $fieldsUa);
			}
		}

		if($sbn)
		{
			$fields = json_decode(file_get_contents($fieldsUrl));
			foreach ($fields->fields as $value) {
				if($term != '')
				{
					if(stripos($value->label,$term) !== false)
					{
						$result[] = array(
							'id' => $value->id,
							'text' => $value->id,
						);
					}
				}
				else {
					$result[] = array(
						'id' => $value->id,
						'text' => $value->id,
					);
				}
			};
		}
		else {
			asort($fields);
			$result = array();
			foreach ($fields as $f) {
				if($term)
				{
					if(stripos($f,$term) === false)
					{
						continue;
					}
				}
				$result[] = array(
					'id' => $f,
					'text' => $f . ( ($section !== 'archivi') ? ' - ' . __T($f) : ''),
				);
			}
		}

		if($result == null)
		{
			return '';
		}
		else {
			return $result;
		}
	}

	private function getArchiveFields($list)
	{
		$array = array();
		foreach($list as $k => $v)
		{
			$array[] = $k;
		}
		return $array;
	}

}
