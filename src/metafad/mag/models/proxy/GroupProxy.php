<?php

class metafad_mag_models_proxy_GroupProxy extends PinaxObject
{
    protected $application;

    function __construct()
    {
        $this->application = pinax_ObjectValues::get('org.pinax', 'application');
    }

    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $docStruProxy = $this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy');
        $id = $proxyParams->id;
        $type = $proxyParams->type;

        $rootId = $docStruProxy->getRootNodeByDocumentId($id)->docstru_rootId;
        $rootNodeDocumentID =  $docStruProxy->getRootNode($rootId)->docstru_FK_document_id;

        $ar = pinax_ObjectFactory::createModel('metafad.mag.models.Model');
        $ar->load($rootNodeDocumentID, 'PUBLISHED_DRAFT');

        $groups = $ar->{'GEN_'.$type.'_group'};

        $result = array();
        //
        foreach($groups as $group) {
          $value = $group->{'GEN_'.$type.'_group_ID'};
          if($term != '')
          {
            if(strpos($value,$term) !== false)
            {
              $result[] = array(
                  'id' => $value,
                  'text' => $value
              );
            }
          }
          else {
            $result[] = array(
                'id' => $value,
                'text' => $value
            );
          }
        }

        return $result;
    }
}
