<?php
class metafad_usersAndPermissions_roles_models_vo_PermissionVO
{
    public $id;
    public $label;
    public $acl;
    public $aclPageTypes;
    public $parentId;

    public function __construct($id, $label, $acl, $aclPageTypes, $parentId = '')
    {
        $this->id = $id;
        $this->label = $label;
        $this->acl = $acl;
        $this->aclPageTypes = $aclPageTypes;
        $this->parentId = $parentId;
    }
}