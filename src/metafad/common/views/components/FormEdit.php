<?php
class metafad_common_views_components_FormEdit extends pinaxcms_views_components_FormEdit
{
    public function render_html_onEnd($value='')
	{
	    parent::render_html_onEnd();

        if ($this->getAttribute('newCode')) {
            $this->_application->addLightboxJsCode();

            $childJs1 = pinax_ObjectFactory::createComponent('pinax.components.JSscript', $this->_application, $this, 'pnx:JSscript', 'js1');
            $childJs1->setAttribute('folder', 'metafad/iccd/views/js');
            $this->addChild($childJs1);

            $childJs2 = pinax_ObjectFactory::createComponent('pinax.components.JSscript', $this->_application, $this, 'pnx:JSscript', 'js2');
            $childJs2->setAttribute('folder', 'metafad/mag/js');
            $this->addChild($childJs2);

            $childJs3 = pinax_ObjectFactory::createComponent('pinax.components.JSscript', $this->_application, $this, 'pnx:JSscript', 'js3');
            $childJs3->setAttribute('folder', 'metafad/common/views/js');
            $this->addChild($childJs3);

    	    $this->initChilds();
    	    $childJs1->render();
    	    $childJs2->render();
    	    $childJs3->render();
        }
	}
}
