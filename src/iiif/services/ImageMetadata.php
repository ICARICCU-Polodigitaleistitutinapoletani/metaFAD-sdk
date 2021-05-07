<?php
class iiif_services_ImageMetadata extends PinaxObject
{
	public function getMetadata($uid)
	{
        $image = new StdClass();
        $image->{'@context'} = 'http://iiif.io/api/image/2/context.json';
        $image->{'@id'} = PNX_HOST.'/rest/iiif/'.urlencode(urlencode($uid));

        $profile = new StdClass();
        $profile->qualities = ["default"];
        $profile->formats = ["jpg"];

        $image->profile = ['http://iiif.io/api/image/2/level2.json', $profile];
        $image->protocol =  "http://iiif.io/api/image";

        $size = $this->getSizes($uid);

        $image->width = $size[0];
        $image->height = $size[1];

        return $image;
    }

    private function getSizes($uid)
    {
        $url = __Config::get('metafad.dam.solr.url');

        $uid = explode('@',$uid)[2];
        $postBody = array(
            'q' => 'id:"'.$uid.'"',
            'start' => 0,
            'rows' => 1,
            'fl' => 'id,height_i,width_i',
            'wt' => 'json'
        );

        $request = __ObjectFactory::createObject('pinax.rest.core.RestRequest', $url . '/select?', 'POST', http_build_query($postBody));
        $request->setTimeout(1000);
        $request->setAcceptType('application/json');
        $request->execute();

        $result = json_decode($request->getResponseBody())->response->docs;
        if ($result) {
            foreach ($result as $doc) {
                return [0 => $doc->width_i, 1 => $doc->height_i];
            }
        }
    }
}
