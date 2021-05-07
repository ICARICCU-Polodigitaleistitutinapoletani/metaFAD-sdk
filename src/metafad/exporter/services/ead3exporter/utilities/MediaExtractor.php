<?php
class metafad_exporter_services_ead3exporter_utilities_MediaExtractor extends PinaxObject
{
    private $dam;
    private $damUrlLocal;

    public function __construct($dam)
    {
        $this->dam = $dam;
        $this->damUrlLocal = metafad_dam_Common::getDamUrlLocal($this->dam);
    }
    public function getMediaValue($mediaData, $attribute)
    {
        $value = json_decode($mediaData)->$attribute;
        if ($attribute == 'id') {
            $value = 'ID_' . $value;
        }
        return $value;
    }

    public function getMetadata($link)
    {
        $res = [];
        $metadata = $this->loadAr($link->id);
        $images = json_decode($metadata->physicalSTRU);
        foreach ($images->image as $img) {
            $mediaId = $img->id;
            $r = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $this->damUrlLocal . $this->dam->getInstance() . '/media/' . $mediaId . '?bytestream=true',  'GET', 'application/json');
            $r->setTimeout(__Config::get('metafad.dam.timeout'));

            $response = $r->execute();

            if ($response) {
                $info = $r->getResponseInfo();
                $body = json_decode($r->getResponseBody());
                if ($info['http_code'] == 200 && !empty($body)) {
                    foreach ($body->bytestream as $b) {
                        if ($b->name === 'original') {
                            $href = $b->url;
                            $linkrole = 'image/' . pathinfo($b->uri, PATHINFO_EXTENSION);
                            $res[$mediaId] = [$href, $linkrole];
                        }
                    }
                } else {
                    // TODO gestire errore
                }
            } else {
                // TODO gestire errore
            }
        }
        if (!empty($res)) {
            return $res;
        }
        return null;
    }

    private function loadAr($id)
    {
        $ar = pinax_ObjectFactory::createModel("metafad.strumag.models.Model");
        $ar->load($id);
        return $ar;
    }
}
