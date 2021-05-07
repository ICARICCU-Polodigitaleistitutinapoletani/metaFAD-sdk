<?php
class metafad_gestioneDati_massiveEdit_views_components_FormEditMassiveChild extends metafad_gestioneDati_massiveEdit_views_components_FormEditMassive
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

            /*$childJs4 = pinax_ObjectFactory::createComponent('pinax.components.JSscript', $this->_application, $this, 'pnx:JSscript', 'js4');
            $childJs4->setAttribute('folder', 'metafad/gestioneDati/massiveEdit/js');
            $this->addChild($childJs4);
            */
    	    $this->initChilds();
    	    $childJs1->render();
    	    $childJs2->render();
            $childJs3->render();
            //$childJs4->render();
        }
	}
}
