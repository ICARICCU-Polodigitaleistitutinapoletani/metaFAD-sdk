<?php
abstract class metafad_common_models_ActiveRecordDocumentIccdCommon extends metafad_common_models_ActiveRecordDocument
{
    function __construct($connectionNumber=0)
    {
        parent::__construct($connectionNumber);

        $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'fulltext', 'type' => 'string', 'index' => 'fulltext', 'onlyIndex' => true)));

        if (__Config::get('metafad.be.hasEcommerce')) {
            $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'ecommerceLicenses', 'type' => 'object')));
            $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'visibility', 'type' => 'string')));
        }

        $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'uniqueIccdId', 'type' => 'string', 'index' => true, 'onlyIndex' => true)));
        $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'isTemplate', 'type' => 'integer', 'index' => true)));
        $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'templateTitle', 'type' => 'string')));
        $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'isValid', 'type' => 'integer')));
    }

    abstract public function getTitle();

    public function getSolrDocument() {
        return parent::getSolrDocument();
    }

    abstract public function getFESolrDocument();

    abstract public function getBeMappingAdvancedSearch();

    abstract public function getBeAdvancedSearchFields();
}