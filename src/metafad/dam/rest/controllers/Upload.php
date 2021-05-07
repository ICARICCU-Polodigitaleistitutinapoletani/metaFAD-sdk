<?php
set_time_limit (0);

class metafad_dam_rest_controllers_Upload extends pinax_rest_core_CommandRest
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute($instance)
    {
        $this->checkPermissionForBackend();

        $uploadsDir = __Config::get('metafad.dam.upload.folder');
        if ($instance && isset($_FILES["file"])) {
            $unique = uniqid();
            @mkdir($uploadsDir);
            $moveFileResult = move_uploaded_file($_FILES['file']['tmp_name'], $uploadsDir . $unique . '_' . $_FILES["file"]['name']);
            if ($moveFileResult == false) {
                return array('http-status' => 400);
            }
            return $unique . '_' . $_FILES["file"]['name'];
        }
        return array('http-status' => 400, 'message' => 'file not uploaded');
    }
}