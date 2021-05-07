<?php
class metafad_opac_controllers_ajax_SaveClose extends metafad_opac_controllers_ajax_Save
{
    public function execute($data, $draft=false)
    {
        $result = parent::execute($data, $draft);

        if ($result['errors']) {
            return $result;
        }

        return array('url' => $this->changeAction(''));
    }
}
