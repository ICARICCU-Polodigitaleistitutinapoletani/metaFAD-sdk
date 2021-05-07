<?php
class metafad_strumag_rest_controllers_List extends pinax_rest_core_CommandRest
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute($search)
    {
        $this->checkPermissionForBackend();

        $it = pinax_ObjectFactory::createModelIterator('metafad.strumag.models.Model');

        if ($search) {
            $it->where('title', '%'.$search.'%', 'ILIKE');
        }

        $result = array();

        foreach ($it as $ar) {
            $result[] = array(
                'id' => $ar->getId(),
                'title' => $ar->title,
            );
        }

        return $result;
    }
}
