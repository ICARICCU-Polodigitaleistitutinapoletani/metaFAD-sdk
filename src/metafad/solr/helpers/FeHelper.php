<?php
class metafad_solr_helpers_FeHelper extends PinaxObject
{
    // Mapping per scheda di dettaglio
    public function detailMapping($data)
    {
    	$moduleId = str_replace('.models.Model', '', $data->__model);
    	$moduleService = pinax_ObjectFactory::createObject('metafad.common.services.ModuleService');
		$elements = $moduleService->getElements($moduleId);
        $values = $this->processElements($elements, $data);

        $doc = new stdClass();
        $doc->data_s = json_encode($values);
		$doc->model_nxs = $data->__model;

		$ar = pinax_ObjectFactory::createModel($data->__model);
		if($ar->load($data->__id))
		{
			$title = $ar->getTitle();
			$doc->title_s = $title;
		}

		$fieldsForSolr = json_decode(__Config::get('metafad.iccd.solr.commonFields'));
		$sortingFields = json_decode(__Config::get('metafad.iccd.solr.sortingFields'));

		$doc = $this->mapSearchFields($values,$doc,$fieldsForSolr,$sortingFields);

		$firstImage = $this->checkForImages($data->FTA,$data->instituteKey);
		if($firstImage)
		{
			$doc->digitale_s = true;
			$doc->digitale_idpreview_s = $firstImage;
		}
		return $doc;
    }

    public function processElements($elements, $content)
    {
        $values = array();

        foreach ($elements as $element) {
            $obj = new StdClass();
            if ($element->children) {
                $value = $content->{$element->name};
                if (is_array($value) && empty($value)) {
                    continue;
                }

                $obj->type = 'group';
                $obj->name = $element->name;
                $obj->label = $element->label;
                $obj->children = array();

                if ($element->minOccurs == 1 && $element->maxOccurs == 1) {
                    $childValue = $this->processElements($element->children, $content);
                    if (!empty($childValue)){
                        $obj->children[] = $childValue;
                    }
                } else {
					//Recupero AUT per link
					if($element->name == 'AUT')
					{
						$__AUT = new stdClass();
						$__AUT->name = '__AUT';
						$__AUT->minOccurs = 0;
						$__AUT->maxOccurs = 1;
						$__AUT->label = "Link autore";
						array_push($element->children,$__AUT);
					}
					//Recupero BIB per link
					if($element->name == 'BIB')
					{
						$__BIB = new stdClass();
						$__BIB->name = '__BIB';
						$__BIB->minOccurs = 0;
						$__BIB->maxOccurs = 1;
						$__BIB->label = "Link bibliografia";
						array_push($element->children,$__BIB);
					}
					if($value)
					{
	                    foreach ($value as $childContent) {
	                        $childValue = $this->processElements($element->children, $childContent);
	                        if (!empty($childValue)){
	                            $obj->children[] = $childValue;
	                        }
	                    }
					}
                }

                if (!empty($obj->children)) {
                    $values[] = $obj;
                }
            } else {
                $value = $content->{$element->name};
                if (!empty($value) || $value === '0' || $value === 0) {
                    $obj->type = 'field';
                    $obj->name = $element->name;
                    $obj->label = $element->label;
                    $obj->value = $value;
                    $values[] = $obj;
                }
            }
        }

        return $values;
    }

    public function searchFieldsMapping($mappingFields, $data)
    {
        $doc = new stdClass();
        return $doc;
    }

	//NB Potrebbe diventare generica per il repo principale
	private function mapSearchFields($data, $doc, $fieldsForSolr, $sortingFields)
	{
		$textField = array();
		$suffix = '_ss';
		$suffixTxt = '_txt';
		$values = $this->getSolrValues($data, $fieldsForSolr);

		foreach ($values as $key => $value) {
			if(!is_array($doc->{$key.$suffix})) {
				$doc->{$key.$suffix} = array();
			}

			if(!is_array($doc->{$key.$suffixTxt})) {
				$doc->{$key.$suffixTxt} = array();
			}

			foreach ($value as $v) {
				if(is_string($v))
				{
					if(in_array($key,$sortingFields))
					{
						if(!$doc->{$key.'_sort_s'})
						{
							$doc->{$key.'_sort_s'} = strtolower($v);
						}
					}
					array_push($doc->{$key.$suffix}, $v);
                    array_push($doc->{$key.$suffixTxt}, strtolower($v));
					array_push($textField, $v);
				}
				else if(is_array($v))
				{
					foreach ($v as $obj)
					{
							$o = get_object_vars($obj);
							if(in_array($key,$sortingFields))
							{
								if(!$doc->{$key.'_sort_s'})
								{
									$doc->{$key.'_sort_s'} = strtolower(end($o));
								}
							}
							array_push($doc->{$key.$suffix}, end($o));
							array_push($doc->{$key.$suffixTxt}, strtolower(end($o)));
							array_push($textField, end($o));
					}
				}
			}
		}
		$doc->text = $textField;
		return $doc;

	}

	//NB Potrebbe diventare generica per il repo principale
	private function getSolrValues($data, $fieldsForSolr)
	{
		$solrValues = array();
		foreach($data as $fd)
		{
			$thisChildren = $fd->children;
			foreach ($thisChildren as $tc) {
				foreach ($tc as $thisChild) {
					if(in_array($thisChild->name,$fieldsForSolr))
					{
						$tcv = $thisChild->value;
						if(is_string($tcv))
						{
							$solrValues[$thisChild->name][] = $tcv;
						}
						else if(is_array($tcv))
						{
							foreach ($tcv as $v) {
								$values = get_object_vars($v);
								$realValue = end($values);
								if(is_string($realValue))
								{
									$solrValues[$thisChild->name][] = $realValue;
								}
								else if(is_object($realValue))
								{
									$solrValues[$thisChild->name][] = $realValue->text;
								}
							}
						}
					}
					else {
						if($thisChild->children)
						{
							foreach ($thisChild->children as $tcc) {
								foreach ($tcc as $k => $child) {
									if(in_array($child->name,$fieldsForSolr))
									{
										$solrValues[$tcc[$k]->name][] = $tcc[$k]->value;
									}
								}
							}
						}
					}
				}
			}

		}

		return $solrValues;
	}

	private function checkForImages($fta,$instituteKey)
	{
		$viewerHelper = pinax_ObjectFactory::createObject('metafad.viewer.helpers.ViewerHelper');
		$dam = $viewerHelper->initializeDam($viewerHelper->getKey($instituteKey));
		$image = json_decode($fta[0]->{'FTA-image'});

		if($image)
		{
			$imageUrl = metafad_dam_Common::replaceUrl($dam->streamUrl($image->id,'thumbnail'));
		}

		return $imageUrl;
	}

}
