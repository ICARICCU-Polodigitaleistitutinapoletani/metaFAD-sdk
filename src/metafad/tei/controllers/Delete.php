<?php
class metafad_tei_controllers_Delete extends metafad_common_controllers_Command
{
    public function execute($id, $model, $recurse = false)
    {
        $this->checkPermissionForBackend('delete');

        $moduleProxy = pinax_ObjectFactory::createObject('metafad.tei.models.proxy.ModuleProxy');

        $it = pinax_ObjectFactory::createModelIterator('metafad.tei.models.Model')
            ->load('getParent', array(':parent' => $id, ':languageId' => pinax_ObjectValues::get('org.pinax', 'languageId')));

        if ($it->count() == 0){
            $moduleProxy->delete($id);
        } else if ($recurse) {
            $moduleProxy->delete($id, $recurse);
        } else {
            $this->logAndMessage('Il manoscritto selezionato contiene schede figlie, non è possibile cancellarlo senza prima aver cancellato tutti gli elementi subordinati', '', PNX_LOG_ERROR);
        }

        pinax_helpers_Navigation::goHere();
    }
}
