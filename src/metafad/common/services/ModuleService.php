<?php
class metafad_common_services_ModuleService extends PinaxObject
{
    public function getElements($moduleId, $admin = 'Admin.xml', $custom = false)
	{
        $modulePath = __Paths::get('APPLICATION_TO_ADMIN').'classes/userModules/'.$moduleId;
		$elementsPath = $modulePath.'/models/elements.json';

		if (file_exists($elementsPath)) {
        	$elements = json_decode(file_get_contents($elementsPath));
		} else {
			$options = array(
    			'cacheDir' => pinax_Paths::get('CACHE_CODE'),
    			'lifeTime' => -1,
    			'readControlType' => '',
    			'fileExtension' => '.json'
    		);

    		$cacheObj = &pinax_ObjectFactory::createObject('pinax.cache.CacheFile', $options );
    		$cacheFileName = $cacheObj->verify( $moduleId, get_class( $this ) );

			// tenta lettura dalla cache prima di parsare Admin
    		if ( $cacheFileName !== false ) {
    			$elements = json_decode( file_get_contents( $cacheFileName ) );
    		} else {
    			$adminPath = $modulePath.'/views/'.$admin;

				$xml = pinax_ObjectFactory::createObject('pinax.parser.XML');
	    		$xml->loadAndParseNS($adminPath);

	    		$elements = array();
	    		$level = 0;

				$xpath = new DOMXPath($xml);
				if(!$custom)
				{
					  $fieldSets = $xpath->query("//pnx:template[@name='form_fields']/pnx:JSTabGroup/*/pnx:Fieldset");
				}
				else
				{
					$fieldSets = $xpath->query("//pnx:template[@name='form_fields']/pnx:Fieldset");
				}

	    		foreach ($fieldSets as $fieldSet) {
        			$elements[] = $this->processFieldSet($fieldSet, $level);
			    }

			    // mette in cache il file elements generato
    			$cacheObj->save( json_encode( $elements ), NULL, get_class( $this ) );
            }
		}

		return $elements;
	}

	public function processLabel($value)
	{
		if (preg_match("/\{i18n\:.*\}/i", $value)) {
			$code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $value);
			$value = pinax_locale_Locale::getPlain($code);
		}
		return $value;
	}

	public function processInput($input, $level)
	{
		$obj = new StdClass;
        $obj->name = $input->getAttribute('id');

		if ($input->hasAttribute('required')) {
         	$obj->required = $input->getAttribute('required') == 'true';
        } else {
	        $obj->required = false;
        }

        $obj->level = $level;
        $obj->label = $this->processLabel($input->getAttribute('label'));

        if ($input->hasAttribute('maxLength')) {
        	$obj->maxLength = $input->getAttribute('maxLength');
        }


		if (!$obj->minOccurs && !$obj->maxOccurs) 
		{
			$obj->minOccurs = $obj->required ? '1' : '0';
			$obj->maxOccurs = '1';
		}

        return $obj;
	}

	public function processFieldSet($fieldSet, $level)
	{
		$obj = new StdClass;
        $obj->name = $fieldSet->getAttribute('id');

	 	if ($fieldSet->hasAttribute('required')) {
         	$required = $fieldSet->getAttribute('required') == 'true';
        } else {
        	$required = false;
        }

        if ($fieldSet->hasAttribute('data')) {
        	$data = $fieldSet->getAttribute('data');
        	foreach (explode(';', $data) as $property) {
        		list($k, $v) = explode('=', $property);
        		if ($k == 'repeatMin') {
        			$obj->minOccurs = $v;
        		}

        		if ($k == 'repeatMax') {
	        		$obj->maxOccurs = $v;
        		}
         	}

         	if (!property_exists($obj, 'maxOccurs')) {
         		$obj->maxOccurs = 'unbounded';
			}

        } else {
	        $obj->minOccurs = $required ? '1' : '0';
	        $obj->maxOccurs = '1';
        }

	    $obj->required = $required;

        $obj->level = $level;
        $obj->label = $this->processLabel($fieldSet->getAttribute('label'));

        $obj->children = array();

        foreach ($fieldSet->childNodes as $child) {
        	if ($child->nodeName == 'pnx:Fieldset') {
        		$obj->children[] = $this->processFieldSet($child, $level+1);
        	} else if ($child->nodeName == 'pnx:Input' && strpos($child->getAttribute('id'),'-element') === false && strpos($child->getAttribute('id'), '__') === false) {
        		$obj->children[] = $this->processInput($child, $level+1);
			} else if ($child->nodeName == 'pnx:List') {
        		$obj->children[] = $this->processInput($child, $level+1);
        	}
			
        }

        return $obj;
	}
}