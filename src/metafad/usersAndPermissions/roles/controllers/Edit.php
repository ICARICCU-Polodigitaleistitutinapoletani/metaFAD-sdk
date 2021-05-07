<?php
class metafad_usersAndPermissions_roles_controllers_Edit extends pinaxcms_contents_controllers_moduleEdit_Edit
{
    public function execute($id)
    {
        if ($id) {
            $c = $this->view->getComponentById('__model');
            $contentProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ActiveRecordProxy');
            $data = $contentProxy->load($id, $c->getAttribute('value'));
            $users = array();

            $it = pinax_ObjectFactory::createModelIterator('metafad.usersAndPermissions.relations.models.Model', 'getUsersForRole', array('params' => array('roleId' => $id)));
            foreach ($it as $ar) {
                $users[] = array(
                    'id' => $ar->user_id,
                    'text' => $ar->user_firstName . ' ' . $ar->user_lastName
                );
            }

            $data['users'] = $users;
            $data['__id'] = $id;

            if(__Session::get('delete'))
            {
                $m = $this->view->getComponentById('message');
                $m->setAttribute('text', 'ATTENZIONE: Il ruolo non può essere cancellato in quanto vi sono alcuni utenti associati. Verificare nell\'apposito campo "Utenti associati".');
                $m->setAttribute('visible', true);
                __Session::remove('delete');
            }

            $this->view->setData($data);
        }
    }
}