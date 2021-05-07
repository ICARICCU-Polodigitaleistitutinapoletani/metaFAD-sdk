<?php
class metafad_common_views_renderer_Preview extends pinaxcms_contents_views_renderer_DocumentTitle
{
    public function renderCell( $key, $value, $row, $columnName )
    {
      if($value)
      {
        $viewerHelper = pinax_ObjectFactory::createObject('metafad.viewer.helpers.ViewerHelper');
        $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
        $dam = $viewerHelper->initializeDam($viewerHelper->getKey($instituteKey));
        return '<img src="'.metafad_dam_Common::replaceUrl($dam->streamUrl($value, 'thumbnail')).'" />';
      }
      else
      {
        return 'N.D.';
      }
    }
}
