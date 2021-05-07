<?php
class metafad_iccd_views_components_FormEdit extends pinaxcms_views_components_FormEdit
{
    function render_html_onEnd($value='')
	{
	    parent::render_html_onEnd();
	    
        $this->_application->addLightboxJsCode();

        $childJs1 = pinax_ObjectFactory::createComponent('pinax.components.JSscript', $this->_application, $this, 'pnx:JSscript', 'js1');
        $childJs1->setAttribute('folder', 'metafad/iccd/views/js');
        $this->addChild($childJs1);

        $childJs2 = pinax_ObjectFactory::createComponent('pinax.components.JSscript', $this->_application, $this, 'pnx:JSscript', 'js2');
        $childJs2->setAttribute('folder', 'metafad/mag/js');
        $this->addChild($childJs2);

	    $this->initChilds();
	    $childJs1->render();
	    $childJs2->render();
	}
}
