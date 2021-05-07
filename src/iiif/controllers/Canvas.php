<?php
class iiif_controllers_Canvas extends iiif_controllers_CachedController
{
    public function execute($uid)
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        return $this->getFromCache($uid, function() use ($uid) {
            $uid = urldecode(urldecode($uid));
            $manifestService = __ObjectFactory::createObject('iiif.services.Manifest');
            $canvas = $manifestService->getCanvasById($uid);
            return $canvas;
        });
    }
}
