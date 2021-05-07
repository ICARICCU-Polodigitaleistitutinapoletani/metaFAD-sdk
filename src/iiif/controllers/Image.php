<?php
class iiif_controllers_Image extends pinax_rest_core_CommandRest
{
    public function execute($uid,$region,$size,$rotation,$quality,$format)
    {
        $uid = urldecode(urldecode($uid));
        $image = __ObjectFactory::createObject('iiif.services.Image');
        $image->serve($uid,$region,$size,$rotation,$quality,$format);
    }
}
