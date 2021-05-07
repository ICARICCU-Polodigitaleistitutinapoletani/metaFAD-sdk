<?php

class metafad_common_views_components_Container extends pinax_components_ComponentContainer
{

	function init()
	{
		$this->defineAttribute('cssClass', 	false, null, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssId', 	false, null, 	COMPONENT_TYPE_STRING);

		parent::init();
	}


	function render_html_onStart()
	{
	    if($this->getAttribute('cssClass') && $this->getAttribute('cssId')){
		    $output = '<div class="'. $this->getAttribute('cssClass') . '" id="'. $this->getAttribute('cssId') . '">';
	    } else if($this->getAttribute('cssClass')){
	        $output = '<div class="'. $this->getAttribute('cssClass') . '">';
	    } else if($this->getAttribute('cssId')){
	        $output = '<div id="'. $this->getAttribute('cssId') . '">';
	    } else{
	        $output = '<div>';
	    }
		$this->addOutputCode($output);
	}

	function render_html_onEnd()
	{
		$output  = '</div>';
		$this->addOutputCode($output);
	}
}
