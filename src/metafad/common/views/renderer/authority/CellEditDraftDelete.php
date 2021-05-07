<?php
class metafad_common_views_renderer_authority_CellEditDraftDelete extends metafad_common_views_renderer_CellEditDraftDelete
{
    public function renderCell($key, $value, $row, $columnName)
    {
        $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
        if ($this->document->instituteKey_s == $instituteKey || $instituteKey == '*') {
            return parent::renderCell($key, $value, $row, $columnName);
        } else {
            $action = $row->hasPublishedVersion ? 'show' : 'showDraft';
            return __Link::makeLinkWithIcon(
                'actionsMVC',
                'btn btn-success btn-flat fa fa-eye',
                array(
                    'title' => __T('PNX_RECORD_EDIT'),
                    'action' => $action, 'id' => $this->document->id),
                    NULL
                );
        }
    }
}
