<?php
set_time_limit (0);

class metafad_dam_rest_controllers_Resize extends pinax_rest_core_CommandRest
{
    function execute($instance, $w)
    {
        if (__Request::get('bytestreamName') == 'original' && (!$w || $w == '*' || $w > __Config::get('metafad.dam.maxResizeWidth') )) {
            return;
        }

        $url = metafad_dam_Common::getDamBaseUrlLocalWithQueryString();
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
