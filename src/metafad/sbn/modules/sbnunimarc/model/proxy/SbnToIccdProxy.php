<?php

class metafad_sbn_modules_sbnunimarc_model_proxy_SbnToIccdProxy extends PinaxObject
{
    //models
    //'metafad.sbn.modules.sbnunimarc.model.Model'
    //'metafad.sbn.modules.authoritySBN.model.Model'
    public function updateSbnToIccd($field,$value,$id,$model)
    {
      $sbn = pinax_ObjectFactory::createModelIterator($model)
            ->where($field,$id)->first();
      if($sbn)
      {
        $sbn->linkedIccd = (string)$value;
        $sbn->save();
      }
    }

    public function updateSbnToIccdSolr($bid, $iccd, $type)
    {
      $url = ($type == 'sbn') ? __Config::get('metafad.service.updateSbnToIccd') : __Config::get('metafad.service.updateSbnToIccdAut') ;
      $body = '?id='.$bid;
      if($iccd)
      {
        $body .= '&linkedIccd='.$iccd;
      }
      $r = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url.$body, 'GET', '', 'application/json');
      $r->execute();
    }

    public function deleteSbnToIccdSolr($iccd, $type, $model)
    {
      $it = pinax_ObjectFactory::createModelIterator($model)
            ->where('linkedIccd',$iccd)->first();
      $id = $it->id;
      $url = ($type == 'sbn') ? __Config::get('metafad.service.updateSbnToIccd') : __Config::get('metafad.service.updateSbnToIccdAut') ;
      $body = '?id='.$id;
      $r = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url.$body, 'GET', '', 'application/json');
      $r->execute();
    }
}
