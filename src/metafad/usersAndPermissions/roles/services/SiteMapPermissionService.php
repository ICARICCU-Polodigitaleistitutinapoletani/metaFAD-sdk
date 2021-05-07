<?php
class metafad_usersAndPermissions_roles_services_SiteMapPermissionService extends metafad_usersAndPermissions_roles_services_AbstractPermissionService
{
    protected $aclNodes;

    public function __construct()
    {
        $application  = pinax_ObjectValues::get('org.pinax', 'application');
        $siteMapIterator = &pinax_ObjectFactory::createObject('pinax.application.SiteMapIterator', $application->getSiteMap());

        while (!$siteMapIterator->EOF) {
            $node = $siteMapIterator->getNode();
            if ($node->getAttribute('adm:acl')) {
                $this->aclNodes[] = $node;
            }
            $siteMapIterator->moveNext();
        }
    }

    protected function fetch()
    {
        $node = $this->aclNodes[$this->pos];

        if (!$node) {
            $this->data = null;
            return;
        }

        $permissionVO = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.roles.models.vo.PermissionVO',
            $node->getAttribute('id'),
            $node->getAttribute('adm:aclLabel') ? $node->getAttribute('adm:aclLabel') : $node->getAttribute('title'),
            $node->getAttribute('adm:acl'),
            $node->getAttribute('adm:aclPageTypes'),
            $node->getAttribute('parentId')
        );

        $this->data = $permissionVO;
        $this->pos++;
    }
}