<?php
class metafad_strumag_views_renderer_CellEditDeleteEcommerce extends metafad_common_views_renderer_CellEditDraftDelete
{

  public function renderCell($key, $value, $row, $columnName)
  {
    parent::renderCell($key, $value, $row, $columnName);
    $draft = ($row->hasPublishedVersion) ? $row->hasDraftVersion : true;

    $output = $this->renderEditButton($key, $row, $row->hasPublishedVersion).
    $this->renderEcommerceButton($key, $row, true).
    $this->renderDeleteButton($key, $row);

    return $output;
  }

  protected function renderEcommerceButton($key, $row, $enabled = true)
  {
      $output = '';
      if ($this->canView && $this->canEdit) {
          $output = __Link::makeLinkWithIcon(
              'actionsMVC',
              __Config::get('pinax.datagrid.action.ecommerceCssClass').($enabled ? '' : ' disabled'),
              array(
                  'title' => 'Ecommerce',
                  'id' => $key,
                  'action' => 'ecommerce',
                  'cssClass' => ($enabled ? '' : ' disabled-button')
              )
          );
      }

      return $output;
  }
}
