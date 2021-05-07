<?php
set_time_limit (0);

class metafad_dam_rest_controllers_Forward extends pinax_rest_core_CommandRest
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute($instance)
    {
        $this->checkPermissionForBackend();

        $url = metafad_dam_Common::getDamBaseUrlLocalWithQueryString();
        $method = __Request::getMethod();
        $postBody = __Request::getBody();

        $request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url, $method, $postBody, 'application/json');
        $request->setTimeout(1000);
        $request->setAcceptType('application/json');
        $request->execute();

        $responseInfo = $request->getResponseInfo();

        $responseBody = str_replace('\\/', '/',  $request->getResponseBody());
        $responseBody = str_replace(metafad_dam_Common::getDamBaseUrlLocal(), metafad_dam_Common::getDamBaseUrl(), $responseBody);

        $resultDecoded = json_decode($responseBody);
        $result = array(
            'http-status' => $responseInfo['http_code'],
            $resultDecoded ? $resultDecoded : $responseBody
        );

        return $result;
    }
}
