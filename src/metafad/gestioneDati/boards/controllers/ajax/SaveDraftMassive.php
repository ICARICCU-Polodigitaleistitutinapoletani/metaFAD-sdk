<?php
class metafad_gestioneDati_boards_controllers_ajax_SaveDraftMassive extends metafad_gestioneDati_boards_controllers_ajax_SaveDraft
{
    public function execute($data = null, $status = 'draft')
    {
        return parent::execute($data, $status);
    }
}
