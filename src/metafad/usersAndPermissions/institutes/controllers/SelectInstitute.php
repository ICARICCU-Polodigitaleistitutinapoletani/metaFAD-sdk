<?php
class metafad_usersAndPermissions_institutes_controllers_SelectInstitute extends metafad_common_controllers_Command
{
    public function execute($instituteKey)
    {
        $prevInstKey = metafad_usersAndPermissions_Common::getInstituteKey();

        $this->checkPermissionForBackend('visible');
        
        metafad_usersAndPermissions_Common::setInstituteKey($instituteKey);

        if($instituteKey != $prevInstKey) {
            $evt = array('type' => 'reloadAcl');
            $this->dispatchEvent($evt);
        }
        
        $this->changePage('linkHome');

    }
}