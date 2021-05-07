<?php
class metafad_tei_controllers_EditDraft extends metafad_tei_controllers_Edit
{
    public function execute($id = null, $sectionType = null, $type = null, $templateID = null, $parentId = null, $status = 'draft')
    {
        parent::execute($id, $sectionType, $type, $templateID, $parentId, 'draft');
    }
}