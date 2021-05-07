<?php
class metafad_strumag_controllers_Edit extends pinaxcms_contents_controllers_moduleEdit_Edit
{
    public function execute($id)
    {
        $c = $this->view->getComponentById('__model');
        __Request::set('model', $c->getAttribute('value'));

        parent::execute($id);
    }
}