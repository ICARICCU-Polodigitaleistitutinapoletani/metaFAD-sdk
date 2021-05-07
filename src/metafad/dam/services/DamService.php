<?php
class metafad_dam_services_DamService extends PinaxObject
{
    protected $damUrl;
    protected $damUrlLocal;

    public function __construct($damInstance = null)
    {
        $this->damUrl = metafad_dam_Common::getDamUrl($damInstance);
        $this->damUrlLocal = metafad_dam_Common::getDamUrlLocal($damInstance);
    }

    public function getAllStreamTypes()
    {
        $r = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $this->damUrlLocal.'/bytestream/getAllTypes');
        $result = $r->execute();
        $response = json_decode($r->getResponseBody());
        return $response;
    }
}
