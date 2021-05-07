<?php
class metafad_opac_views_renderer_InstituteCell extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
  function renderCell($key, $value, $row, $columnName)
  {
    parent::renderCell($key, $value, $row, $columnName);
    $instituteProxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.institutes.models.proxy.InstitutesProxy');
    $data = $instituteProxy->getInstituteVoByKey($value);
    return $data->institute_name;
  }
}
