<?php

class metafad_mag_views_components_Input extends pinax_components_Input
{
	function render_html()
	{
		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		$attributes['name'] 		= $this->getOriginalId();
		$attributes['disabled'] 	= $this->getAttribute('disabled') ? 'disabled' : '';
		$attributes['readonly'] 	= $this->getAttribute('readOnly') ? 'readonly' : '';
		$attributes['title'] 		= $this->getAttributeString('title');
		$attributes['placeholder'] 		= $this->getAttributeString('placeholder');
		if ( empty( $attributes['title'] ) )
		{
			$attributes['title'] 		= $this->getAttributeString('label');
		}
		$attributes['class'] 		= $this->getAttribute('cssClass');
		$attributes['class'] 		.= (!empty($attributes['class']) ? ' ' : '').($this->getAttribute('required') ? 'required' : '');

		if ($this->getAttribute('type')=='multiline')
		{
			$attributes['cols'] 		= $this->getAttribute('cols');
			$attributes['rows'] 		= $this->getAttribute('rows');
			$attributes['wrap'] 		= $this->getAttribute('wrap');

			$output  = '<textarea '.$this->_renderAttributes($attributes).'>';
			$output .= $this->encodeOuput($this->_content);
			$output .= '</textarea>';

			$this->addTinyMCE( true );
		}
		else
		{
			$attributes['type'] 		= $this->getAttribute('type');
			$attributes['maxLength'] 	= $this->getAttribute('maxLength');
			$attributes['size'] 		= $this->getAttribute('size');
			$attributes['value'] 		= $this->encodeOuput(is_string($this->_content) ? $this->_content : json_encode($this->_content));

			$output  = '<input style="display:none"'.$this->_renderAttributes($attributes).'/>';
		}

		$label = $this->getAttributeString('label') ? : '';
		if ($label) {
			$cssClassLabel = $this->getAttribute( 'cssClassLabel' );
			$cssClassLabel .= ( $cssClassLabel ? ' ' : '' ).($this->getAttribute('required') ? 'required' : '');
			if ($this->getAttribute('wrapLabel')) {
				$label = pinax_helpers_Html::label($this->getAttributeString('label'), $this->getId(), true, $output, array('class' => $cssClassLabel ), false);
				$output = '';
			} else {
				$label = pinax_helpers_Html::label($this->getAttributeString('label'), $this->getId(), false, '', array('class' => $cssClassLabel ), false);
			}
		}
		$this->addOutputCode($this->applyItemTemplate($label, $output));
	}

	private function addTinyMCE( $attachToElement )
	{
		if ($this->getAttribute('htmlEditor')===true)
		{
			$rootComponent = &$this->getRootComponent();

			if (!pinax_ObjectValues::get('pinax.JS.TinyMCE', 'add', false))
			{
				pinax_ObjectValues::set('pinax.JS.TinyMCE', 'add', true);

				$rootComponent->addOutputCode(pinax_helpers_JS::linkCoreJSfile('tiny_mce.js?v='.PNX_CORE_VERSION, 'tiny_mce/', false), 'head');
				$rootComponent->addOutputCode(pinax_helpers_JS::linkCoreJSfile('Pinax_tiny_mce.js?v='.PNX_CORE_VERSION), 'head', true);

				$imgStyles = __Config::get( 'TINY_MCE_IMG_STYLES' );
				$imgSizes = __Config::get( 'TINY_MCE_IMG_SIZES' );
				$templates = __Config::get( 'TINY_MCE_TEMPLATES' );
				$imgStyles = $imgStyles ? : '""';
				$imgSizes = $imgSizes ? : '""';
				$templates = $templates ? : '""';

				$jsCode = 'Pinax.tinyCSS = "'.__Config::get( 'TINY_MCE_CSS' ).'";';
				$jsCode .= 'Pinax.tinyMCE_plugins = "'.__Config::get( 'TINY_MCE_DEF_PLUGINS' ).'";';
				$jsCode .= 'Pinax.tinyMCE_btn1 = "'.__Config::get( 'TINY_MCE_BUTTONS1' ).'";';
				$jsCode .= 'Pinax.tinyMCE_btn2 = "'.__Config::get( 'TINY_MCE_BUTTONS2' ).'";';
				$jsCode .= 'Pinax.tinyMCE_btn3 = "'.__Config::get( 'TINY_MCE_BUTTONS3' ).'";';
				$jsCode .= 'Pinax.tinyMCE_styles = '.__Config::get( 'TINY_MCE_STYLES' ).';';
				$jsCode .= 'Pinax.tinyMCE_imgStyles = '.$imgStyles.';';
				$jsCode .= 'Pinax.tinyMCE_imgSizes = '.$imgSizes.';';
				$jsCode .= 'Pinax.tinyMCE_templates = '.$templates.';';
				$jsCode .= 'Pinax.tinyMCE_allowLinkTarget = '.(__Config::get( 'TINY_MCE_ALLOW_LINK_TARGET' ) ? 'true' : 'false').';';
				$jsCode .= 'Pinax.tinyMCE_queryStringEnabled = '.(__Config::get( 'pinaxcms.pagePicker.queryStringEnabled' ) ? 'true' : 'false').';';
				$validElements = __Config::get( 'TINY_MCE_VALID_ELEMENTS' );
        		$jsCode .= 'Pinax.tinyMCE_validElements = "'.($validElements ? : '').'";';
				$plugins = __Config::get( 'TINY_MCE_PLUGINS' );
				if ( $plugins ) {
					$jsCode .= 'Pinax.tinyMCE_plugins .= ",'.$plugins.'";';
				}
				$tableClassList = __Config::get('TINY_MCE_TABLE_CLASS_LIST');
				$jsCode .= 'Pinax.tinyMCE_tableClassList = "'.$tableClassList.'";';

				$tinyMCEoptions = __Config::get('TINY_MCE_OPTIONS');
				$jsCode .= 'Pinax.tinyMCE_customOptions = '.($tinyMCEoptions ? : '{}').';';

				$rootComponent->addOutputCode(pinax_helpers_JS::JScode( $jsCode ), 'head');
			}

			if (!is_null($this->getAttribute('adm:tinyMCEplugin')))
			{
				$pluginsNames = explode( ',', $this->getAttribute('adm:tinyMCEplugin') );
				$pluginsPaths = array();
				for( $i=0; $i < count( $pluginsNames ); $i++ )
				{
					$pos = strrpos( $pluginsNames[ $i ], "/" );
					if ( $pos !== false )
					{
						$pluginsPaths[] = '../../../../../../'.$pluginsNames[ $i ];
						$pluginsNames[ $i ] = substr( $pluginsNames[ $i ], $pos + 1 );
					}
					else
					{
						$pluginsPaths[] = $pluginsNames[ $i ];
					}
				}
				if ( count( $pluginsPaths ) )
				{
					$jsCode = 'Pinax.tinyMCE_plugins += ",'.implode( ',', $pluginsPaths ).'";';
					$jsCode .= 'Pinax.tinyMCE_pluginsNames += ",'.implode( ',', $pluginsNames ).'";';
					$rootComponent->addOutputCode(pinax_helpers_JS::JScode( $jsCode ), 'head');
				}
			}

			if ( $attachToElement )
			{
				$id = $this->getId();
				$jsCode = <<< EOD
jQuery(function(){
	var options = Pinax.tinyMCE_options;
	options.mode = "exact";
	options.elements = '$id';
	tinyMCE.init( options );
});
EOD;
				//$this->addOutputCode(pinax_helpers_JS::JScode( $jsCode ));
			}
		}
	}
}
