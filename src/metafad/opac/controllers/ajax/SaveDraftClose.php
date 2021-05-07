<?php
class  metafad_opac_controllers_ajax_SaveDraftClose extends metafad_opac_controllers_ajax_SaveDraft
{
    public function execute($data)
    {
        $result = parent::execute($data);

        if ($result['errors']) {
            return $result;
        }

        return array('url' => $this->changeAction(''));
    }
}
