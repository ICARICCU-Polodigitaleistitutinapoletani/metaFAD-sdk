<?php

/**
 * Salva l'input nel database usando il proxy dell'archivistico
 */
class metafad_common_importer_operations_SaveArchivisticoRoot extends metafad_common_importer_operations_LinkedToRunner
{
    private $overwrite = false;
    private $oaiImport;
    private $oaiId;
    private $type;

    function __construct($params, $runner)
    {
        $this->overwrite = $params->overwrite == '1';
        $this->oaiImport = $params->oaiImport == true ? true : '';
        $this->oaiId = $params->oaiId;
        $this->type = $params->type;
        parent::__construct($params, $runner);
    }

    /**
     * Riceve in input una stdClass con i campi:<br>
     * <ul>
     * <li>data = una stdClass da passare all'archiviProxy</li>
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
        /**
         * @var $arcProxy archivi_models_proxy_ArchiviProxy
         */
        $arcProxy = $this->getOrSetDefault("archiviProxy", pinax_ObjectFactory::createObject("archivi_models_proxy_ArchiviProxy"));
        $arcProxy->setRetryWithDraftOnInvalidate(true);
        $arcProxy->isImportProcess();
        if ($this->oaiImport) {
            $input->data->readOnly = true;
            if ($this->oaiId && $this->type != 'massive') {
                $input->data->oaiUrl = $this->oaiId;
            }
        }
        $data = clone $input->data;
        //Con la coda abilitata attualmente si riscontrano problemi nella generazione del numero di ordinamento provvisorio progressivo
        //$arcProxy->setRetryWithDraftOnInvalidate(true)->setQueueSize(500);
        $it = pinax_ObjectFactory::createModelIterator($input->data->__model)->where('externalID', $input->data->externalID)->where('instituteKey', metafad_usersAndPermissions_Common::getInstituteKey());
        $c = $it->count();
        if (!$this->overwrite) {
            foreach ($it as $ar) {
                $ar->externalID = '';
                $res = $ar->save(null, false, 'PUBLISHED');
            }
        } else {
            if ($it->count() > 0) {
                $ar = $it->first();
                $data = $ar->getRawData();
                $data->__id = $data->document_id;
                $data->__model = $input->data->__model;
                foreach ($input->data as $k => $v) {
                    if ($this->oaiImport && $k == 'parent') {
                        continue;
                    }
                    $data->$k = $v;
                }
            }
        }
        if (!$this->overwrite) {
            if (($data->__model) == 'archivi.models.ComplessoArchivistico') {
                $data->visibility = 'rd';
            } else {
                $data->visibility = 'rdv';
            }
        }
        $res = $arcProxy->save($data);
        $id = $res['set']['__id'];

        return (object) array("data" => $input->data, "id" => $id);
    }

    function validateInput($input)
    {
    }
}
