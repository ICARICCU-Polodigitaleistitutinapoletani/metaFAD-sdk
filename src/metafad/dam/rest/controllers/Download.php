<?php
set_time_limit (0);

class metafad_dam_rest_controllers_Download extends pinax_rest_core_CommandRest
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute($instance)
    {
        if (__Request::get('bytestreamName') == 'original') {
            $this->checkPermissionForBackend();
        }


        $url = metafad_dam_Common::getDamBaseUrlLocalWithQueryString();

        if (!__Config::get('metafad.dam.hasDownload')) {
            $url = metafad_dam_Common::getDamBaseUrlLocal().__Request::get('instance').'/resize/'.__Request::get('mediaId').'/original?h=*&w='.__Config::get('metafad.dam.maxResizeWidth');
        }

        $method = __Request::getMethod();
        $postBody = __Request::getBody();

        $request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url, $method, $postBody, 'application/json');
        $request->setTimeout(1000);
        $request->setAcceptType('application/json');
        $request->execute();

        while (ob_get_level()) {
            ob_end_clean();
        }

        foreach ($request->getResponseHeaders() as $header) {
            if (strpos($header, 'Set-Cookie')!==false) continue;
            header($header);
        }

        echo $request->getResponseBody();
        exit;
    }
}
