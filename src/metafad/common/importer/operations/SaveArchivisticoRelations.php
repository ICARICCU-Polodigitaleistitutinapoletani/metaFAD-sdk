<?php

/**
 * Salva l'input nel database usando il proxy dell'archivistico
 */
class metafad_common_importer_operations_SaveArchivisticoRelations extends metafad_common_importer_operations_LinkedToRunner
{
    private $model;
    private $oaiImport;
    private $overwrite = false;
    private $oaiPrefix;
    private $type;

    function __construct($params, $runner)
    {
        $this->model = $params->model;
        $this->overwrite = $params->overwrite == '1';
        $this->oaiImport = $params->oaiImport == true ? true : '';
        $this->oaiPrefix = $params->prefixOAI;
        $this->type = $params->type;
        parent::__construct($params, $runner);
    }

    /**
     * Riceve in input una stdClass con i campi:<br>
     * <ul>
     * <li>data = una stdClass</li>
     * </ul>
     * <br>
     * Restituisce in output una stdClass con i campi:<br>
     * <ul>
     * <li>data = la stessa in input</li>
     * <li>id = identificatore restituito dal salvataggio</li>
     * </ul>
     * @param stdClass $input
     * @return stdClass
     */
    function execute($input)
    {
        $arcProxy = $this->getOrSetDefault("archiviProxy", pinax_ObjectFactory::createObject("archivi_models_proxy_ArchiviProxy"));
        $arcProxy->setRetryWithDraftOnInvalidate(true);
        $arcProxy->isImportProcess();
        //if ($this->charToFix) {
        //$input->data->externalID = str_replace($this->charToFix, $this->replace, $input->data->externalID);
        //}
        if ($this->oaiImport) {
            $input->data->readOnly = true;
            if ($this->type != 'massive') {
                $input->data->oaiUrl = $this->buildOAIurl($input->data->externalID);
            }
        }
        if (!$this->overwrite) {
            $input->data->externalID .= '##current##';
        }
        $it = pinax_ObjectFactory::createModelIterator($this->model)->where('externalID', $input->data->externalID)->where('instituteKey', metafad_usersAndPermissions_Common::getInstituteKey());
        if ($it->count() === 0 && isset($input->data->externalID2)) {
            $it = pinax_ObjectFactory::createModelIterator($this->model)->where('externalID2', $input->data->externalID2)->where('instituteKey', metafad_usersAndPermissions_Common::getInstituteKey());
        }
        //TODO PORTARE ACRONIMO ALL'ESTERNO
        if ($it->count()) {
            $ar = $it->first();
            $rawData = $ar->getRawData();
            $rawData->__id = $rawData->document_id;
            $rawData->__model = $input->data->__model;
            foreach ($input->data as $k => $v) {
                //$ar->$k = $v
                $rawData->$k = $v;
            }
            //$id = $ar->save(null, false, 'PUBLISHED');
            $res = $arcProxy->save($rawData);
            $ar->deleteStatus('OLD');
            $id = $res['set']['__id'];
        } else {
            $input->data->acronimoSistema = 'ICAR';
            $res = $arcProxy->save($input->data);
        }
        unset($arcProxy);
        return (object) array("data" => $input->data, "id" => $id);
    }

    function validateInput($input)
    {
    }

    function buildOAIurl($id)
    {
        if ($this->type == 'SIAS') {
            $url = __Config::get('OAI_SIAS');
            $idSection = 'oai:sias.archivi.beniculturali.it';
        } elseif ($this->type == 'SIUSA') {
            $url = __Config::get('OAI_SIUSA');
            $idSection = 'oai:siusa.archivi.beniculturali.it';
        }
        $id = str_replace('-', ':', $id);
        $url .= "?verb=GetRecord&identifier=$idSection:$id&metadataPrefix=" . $this->oaiPrefix;
        return $url;
    }
}
