<?php
class metafad_mag_controllers_ajax_StruFormat extends metafad_common_controllers_ajax_CommandAjax
{
  public function execute($value)
  {
    $result = $this->checkPermissionForBackend('visible');
    if (is_array($result)) {
      return $result;
    }

    if ($value) {
      $struId = $value['id'];
      $struText = $value['text'];
      $strumag = pinax_ObjectFactory::createModel('metafad.strumag.models.Model');
      if ($struId) {
        $strumag->load($struId);
      }
      $physicalStru = json_decode($strumag->physicalSTRU);
      $logicalStru = json_decode($strumag->logicalSTRU);
      $strumagHelper = pinax_ObjectFactory::createObject('metafad.mag.helpers.StrumagHelper');

      //Estraggo la struttura dello STRU logica
      $tree = $strumagHelper->createTree($logicalStru, 0);

      $elements = $strumagHelper->createShowElement($struId);

      $elements .= $strumagHelper->getElementsStru($physicalStru);

      $elements .= '</div>';
    }
    return array($tree, $elements);
  }

  public function createTree($element, $level)
  {
    $output = '';
    foreach ($element as $e) {
      $folder = $e->folder;
      $faclass = ($folder == true) ? 'fa-folder-o' : 'fa-align-left';
      $input = ($level == 0) ? '<input type="checkbox"/>' : '';
      if ($folder) {
        $output .= '<li class="js-stru js-showElements" data-key="' . $e->key . '">
        <div>' . $input . '<i class="fa fa-caret-right carets js-caret"></i>
        <i class="fa ' . $faclass . '" aria-hidden="true"></i>
        ' . $e->title . '</div>';
      } else {
        $output .= '<li class="element-clickable js-showElements" data-key="' . $e->key . '">
        <div class="strutree-title-container">' . $input . '<i class="fa ' . $faclass . '" aria-hidden="true"><span class="strutree-title">' . $e->title . '</span></i></div>
        </li>';
      }

      $children = $e->children;
      if ($children) {
        $output .= '<ul class="">';
        $output .= $this->createTree($children, $level + 1);
        $output .= '</ul>';
      }

      if ($folder) {
        $output .= '</li>';
      }
    }
    return $output;
  }
}
