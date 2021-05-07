<?php
class metafad_common_views_renderer_CellEditDeleteMassive extends metafad_common_views_renderer_CellEditDraftDelete
{
  public function renderCell($key, $value, $row, $columnName)
  {
    parent::renderCell($key, $value, $row, '');
    $draft = ($row->hasPublishedVersion) ? $row->hasDraftVersion : true;

    $output = $this->renderEditButton($key, $row, $row->hasPublishedVersion).
    $this->renderDeleteButton($key, $row) ;

    return $output;
  }

  protected function renderEditButton($key, $row, $enabled = true)
  {
      $output = '';
      if ($this->canView && $this->canEdit) {
          $output = __Link::makeLinkWithIcon(
              'actionsMVCEditMassive',
              __Config::get('pinax.datagrid.action.editCssClass').($enabled ? '' : ' disabled'),
              array(
                  'title' => __T('PNX_RECORD_EDIT'),
                  'id' => $row->idList,
                  'pageId' => $row->routing,
                  'cssClass' => ($enabled ? '' : ' disabled-button')
              )
          );
      }

      return $output;
  }

}
