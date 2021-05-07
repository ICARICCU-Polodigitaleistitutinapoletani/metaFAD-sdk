<?php
class metafad_common_models_ActiveRecordDocument extends pinax_dataAccessDoctrine_ActiveRecordDocument
{
    function __construct($connectionNumber=0)
    {
        parent::__construct($connectionNumber);

        $this->addField(pinax_dataAccessDoctrine_DbField::create(array('name' => 'instituteKey', 'type' => 'string', 'index' => true)));

        // aggiunge a tutti i model la chiave dell'istituto
        $this->instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
    }

    protected function insert($values = NULL, $status=self::STATUS_DRAFT, $comment)
    {
        $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
        $this->instituteKey = $instituteKey;
        return parent::insert($values, $status, $comment);
    }

    protected function insertDetailOnly($values = NULL, $currentStatus, $newStatus = self::STATUS_DRAFT, $comment='')
    {
        $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
        if ( ($instituteKey == '*' && !$this->instituteKey) || ($instituteKey != '*' && $instituteKey != null)) {
            $this->instituteKey = $instituteKey;
        }

        if (!$this->instituteKey) {
            $session = __Session::getAllAsArray();
            metafad_Metafad::logError('save error institute is empty:'.json_encode($session), 'internal');

            throw metafad_common_models_ActiveRecordDocumentException::instituteKeyEmpty();
        }

        return parent::insertDetailOnly($values, $currentStatus, $newStatus, $comment);
    }

    public function getSolrDocument()
    {
        $solrModel = array(
            'instituteKey' => 'instituteKey_s'
        );

        return $solrModel;
    }

    public function canTranslate()
    {
        return true;
    }

    function loadFromArray($values, $useSet=false)
    {
        parent::loadFromArray($values, $useSet);

        // carica i valori delle schede collegate
        foreach ($this->data as $k => $v) {
            $field = $this->getField($k);
            if ($field->option) {
                $subField = $this->$k->{'__'.$k};
                foreach ((array)$subField as $referred) {
                    $ar = pinax_ObjectFactory::createModel($field->option);
                    $ar->load($referred->id);
                    $referred->values = $ar->getValuesAsArray();
                }
            }
        }
    }
}