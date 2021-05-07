<?php
class metafad_strumag_rest_controllers_Load extends pinax_rest_core_CommandRest
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute($id)
    {
        $this->checkPermissionForBackend();

        if ($id) {
            $ar = pinax_ObjectFactory::createModel('metafad.strumag.models.Model');
            if ($ar->load($id)) {
                $vo = pinax_ObjectFactory::createObject('metafad_strumag_models_vo_STRUMAGVO', $ar);
                return $vo;
            } else {
                return array(
                    'http-status' => 404,
                    'message' => 'Not found'
                );
            }
        } else {
            return array(
                'http-status' => 400,
                'message' => 'Invalid request: missing required parameters'
            );
        }
    }
}
