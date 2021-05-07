<?php
abstract class metafad_common_models_ActiveRecordDocumentIccdAUT extends metafad_common_models_ActiveRecordDocumentIccdCommon
{
    function __construct($connectionNumber=0)
    {
        parent::__construct($connectionNumber);

        if (__Config::get('metafad.be.hasSBN')) {
            $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'VID', 'type' => 'string', 'index' => true)));
        }
    }

    public function getSolrDocument() {
        return parent::getSolrDocument();
    }
    
}