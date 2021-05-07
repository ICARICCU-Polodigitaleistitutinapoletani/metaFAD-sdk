<?php
class metafad_print_views_components_Input extends pinax_components_Input
{
	function render_html()
	{
		$output  = '<div>'.$this->encodeOuput($this->_content).'</div>';

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
}