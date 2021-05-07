<?php
class metafad_opac_controllers_Delete extends pinaxcms_contents_controllers_moduleEdit_Delete
{
    public function execute($id, $model)
    {
        $evt = array('type' => 'deleteRecord', 'data' => $id);
        $this->dispatchEvent($evt);

        parent::execute($id, $model);
    }
}
