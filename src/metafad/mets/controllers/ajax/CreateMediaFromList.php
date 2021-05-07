<?php
class metafad_mets_controllers_ajax_CreateMediaFromList extends metafad_mag_controllers_ajax_CreateMediaFromList
{
  public function execute($stru = 0, $key = 0, $id = 0, $type = 'mets')
  {
    return parent::execute($stru, $key, $id, 'mets');
  }
}
