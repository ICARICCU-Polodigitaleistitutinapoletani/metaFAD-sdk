<?php
class iiif_controllers_ImageMetadata extends iiif_controllers_CachedController
{
    public function execute($uid)
    {
        header('Content-Type: application/json');
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        return $this->getFromCache($uid, function() use ($uid) {
            $uid = urldecode(urldecode($uid));
            $imageMetadata = __ObjectFactory::createObject('iiif.services.ImageMetadata');
            $imageMetadata =  $imageMetadata->getMetadata($uid);
            echo json_encode($imageMetadata, JSON_PRETTY_PRINT);
            exit;
        });
    }
}
