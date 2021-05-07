<?php
abstract class metafad_common_models_ActiveRecordDocumentIccd extends metafad_common_models_ActiveRecordDocumentIccdCommon
{
    protected $connectionNumber;

    function __construct($connectionNumber=0)
    {
        $this->connectionNumber = $connectionNumber;
        parent::__construct($connectionNumber);

        if (__Config::get('metafad.be.hasSBN')) {
            $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'BID', 'type' => 'string', 'index' => true)));
        }
    }

    public function setupTable($moduleId)
    {
        $this->_className = $moduleId.'.models.Model';
        $this->setTableName($moduleId, pinax_dataAccessDoctrine_DataAccess::getTablePrefix($this->connectionNumber));
        $this->setType($moduleId);
    }

    public function getSolrDocument() {
        $solrModel = array(
            '__id' => 'id',
            $this->_className  => 'document_type_t',
            'updateDateTime' => 'update_at_s',
            'document' => 'doc_store',
            'isValid' => 'isValid_i',
        );

        return array_merge(parent::getSolrDocument(), $solrModel);
    }

    public function getFESolrDocument()
    {
        $solrModel = array(
            '__id' => 'id',
            $this->_className => 'document_type_t',
            'updateDateTime' => 'update_at_s',
            'document' => 'doc_store',
            'isValid' => 'isValid_i',
        );

        return $solrModel;
    }

    public function getBeMappingAdvancedSearch()
    {

        $solrModel = array(
            '__id' => 'id',
            $this->_className => 'document_type_t',
            'updateDateTime' => 'update_at_s',
            'document' => 'doc_store',
            'isValid' => 'isValid_i',
        );

        return $solrModel;
    }
}