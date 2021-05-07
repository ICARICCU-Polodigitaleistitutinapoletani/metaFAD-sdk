<?php
class metafad_mag_views_renderer_CellEditDraftDeleteDownload extends metafad_common_views_renderer_CellEditDraftDelete
{

    protected function renderEditButton($key, $row, $enabled = true)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon(
                'actionsMVC',
                __Config::get('pinax.datagrid.action.editCssClass') . ($enabled ? '' : ' disabled'),
                array(
                    'title' => __T('PNX_RECORD_EDIT'),
                    'id' => $key,
                    'action' => 'edit',
                    'cssClass' => ($enabled ? '' : ' disabled-button')
                )
            );
        }

        return $output;
    }

    protected function renderDeleteButton($key, $row)
    {
          $output = '';
          if ($this->canView && $this->canDelete) {
              $output .= __Link::makeLinkWithIcon( 'actionsMVCDelete',
                                                              __Config::get('pinax.datagrid.action.deleteCssClass'),
                                                              array(
                                                                  'title' => __T('PNX_RECORD_DELETE'),
                                                                  'id' => $key,
                                                                  'model' => 'metafad.mag.models.Model',
                                                                  'action' => 'delete'  ),
                                                              __T('PNX_RECORD_MSG_DELETE') );
          }

      return $output;
    }

    protected function renderDownloadButton($key, $row)
    {
      //TODO rimuove classe linkDisabled una volta introdotto il download
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output .= __Link::makeLinkWithIcon('actionsMVC',
                __Config::get('pinax.datagrid.action.downloadCssClass'),
                array(
                    'title' => __T('Genera XML'),
                    'id' => $key,
                    'model' => $row->className,
                    'action' => 'download',
                    'target' => '_blank'
                ));
        }

        return $output;
    }


    public function renderCell($key, $value, $row, $columnName)
    {

        $draft = ($row->hasPublishedVersion) ? $row->hasDraftVersion : true;

        $output = $this->renderEditButton($key, $row, $row->hasPublishedVersion).
                $this->renderEditDraftButton($key, $row, $draft).
                $this->renderDeleteButton($key, $row) .
                $this->renderDownloadButton($key, $row);

        return $output;
    }

}
