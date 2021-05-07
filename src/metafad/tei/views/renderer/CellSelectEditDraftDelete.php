<?php
class metafad_tei_views_renderer_CellSelectEditDraftDelete extends metafad_common_views_renderer_CellEditDraftDelete
{
    private function getPageTypeFromModelName($modelname){
        $modelNameSplit = explode(".",$modelname);
        return strtolower("tei-".end($modelNameSplit));
    }

    protected function renderEditDraftButton($key, $row, $enabled = true)
    {
        $output = '';
        if ($this->canView && $this->canEditDraft) {
            $output = __Link::makeLinkWithIcon(
                'archiviMVC',
                __Config::get('pinax.datagrid.action.editDraftCssClass').($enabled ? '' : ' disabled'),
                array(
                    'title' => __T('PNX_RECORD_EDIT_DRAFT'),
                    'id' => $key,
                    'action' => 'editDraft',
                    'sectionType' => $row->sectionType_s,
                    'pageId' => $this->getPageTypeFromModelName($row->document_type_t[0]),
                    'cssClass' => ($enabled ? '' : ' disabled-button')
                )
            );
        }

        return $output;
    }

    function renderEditButton($key, $row, $enabled=true)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon(
                'archiviMVC',
                __Config::get('pinax.datagrid.action.editCssClass').($enabled ? '' : ' disabled'),
                array(
                    'title' => __T('PNX_RECORD_EDIT'),
                    'id' => $key,
                    'action' => 'edit',
                    'sectionType' => $row->sectionType_s,
                    'pageId' => $this->getPageTypeFromModelName($row->document_type_t[0]),
                    'cssClass' => ($enabled ? '' : ' disabled-button')
                )
            );
        }

        return $output;
    }

    function renderDeleteButton($key, $row){
        $output = '';
        if ($this->canView && $this->canDelete) {
            $output .= __Link::makeLinkWithIcon(
                'archiviMVCDelete',
                __Config::get('pinax.datagrid.action.deleteCssClass'),
                array(
                    'title' => __T('PNX_RECORD_DELETE'),
                    'id' => $key,
                    'model' => $row->document_type_t[0],
                    'pageId' => $this->getPageTypeFromModelName($row->document_type_t[0]),
                    'action' => 'delete'
                ),
                __T('PNX_RECORD_MSG_DELETE')
            );
        }

        return $output;
    }

    public function renderCell($key, $value, $row, $columnName)
    {
        $this->loadAcl($key);

        $output = $this->renderEditButton($key, $this->document, $row->hasPublishedVersion).
                  $this->renderEditDraftButton($key, $this->document, $row->hasDraftVersion).
                  $this->renderDeleteButton($key, $this->document);

        return $output;
    }
}


