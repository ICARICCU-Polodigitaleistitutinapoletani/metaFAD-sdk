<?php
class metafad_usersAndPermissions_roles_Acl extends pinax_application_Acl
{
    protected $roles;
    protected $aclMatrix;

    function __construct($id, $groupId)
    {
        parent::__construct($id, $groupId);

        $this->addEventListener('reloadAcl', $this);

        $this->aclMatrix = array();

        if ($id)  {
            // TODO ora la matrice è memorizzata nella sessione
            // e non può essere invalidata dal gestore dei ruoli per tutti gli utenti
            $roles = __Session::get('pinax.roles');
            if (!empty($roles)) {
                $this->roles = $roles;
                $this->aclMatrix = __Session::get('pinax.aclMatrix');
            } else {
                $this->reloadAcl();
            }
        }
    }

    public function reloadAcl()
    {
        //TODO-FIX
        if (!$this->id)  {
            $this->id = pinax_ObjectValues::get('org.pinax', 'userId');
            if (!$this->id) {
                return;
            }
        }

        if ($this->id) {
            $relationsProxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.relations.models.proxy.RelationsProxy');
            list($this->roles, $this->aclMatrix) = $relationsProxy->getPermissions($this->id);
            if (!empty($this->aclMatrix)) {
                __Session::set('pinax.roles', $this->roles);
                __Session::set('pinax.aclMatrix', $this->aclMatrix);
            }
        }
    }

    function acl($name, $action, $default=false, $options=null)
    {
        if ($name == 'utenti-e-permessi-selezione-istituto' || strtolower($name) == 'home') {
            return true;
        }

        $name = $name=='*' ? strtolower($this->application->getPageId()) : strtolower($name);

        $popupPageType = json_decode(__Config::get("metafad.archive.popup"));
        if(array_key_exists($name, $popupPageType)) {
            $name = $popupPageType->$name;
        }

        if (isset($this->aclMatrix[$name])) {
            $result = $this->aclMatrix[$name]['all'] || $this->aclMatrix[$name][strtolower($action)];
        } else {
            $result = is_null($default) ? false : $default;
        }
        return $result;
    }

    function inRole($roleId)
    {
        return $this->roles[$roleId];
    }

    function getRoles()
    {
        return array_keys($this->roles);
    }

    function invalidateAcl()
    {
        __Session::set('pinax.roles', null);
        __Session::set('pinax.aclMatrix', null);
    }
}