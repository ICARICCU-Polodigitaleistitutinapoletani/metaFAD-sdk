<?php

class metafad_common_importer_operations_ReadXMLResponseRelations extends metafad_common_importer_operations_LinkedToRunner
{
    protected $suppressErrors = false;
    protected $keepDefaultNS = false;
    protected $prefix;
    protected $logFile = "";
    protected $warningFile;
    protected $configFilePath;
    protected $pathsAndValueValidation = false;
    protected $pathsValidation;
    protected $type;
    protected $ids;

    function __construct(stdClass $params, metafad_common_importer_MainRunner $runnerRef)
    {
        $this->type = $params->type;
        $this->ids = $params->ids;
        $this->suppressErrors = $params->suppress === "true";
        $this->keepDefaultNS = $params->keepDefaultNS === "true";
        $this->pathsAndValueValidation = $params->pathsAndValueValidation == "true";
        $this->prefix = $params->prefix;
        $this->logFile = "./export/icar-import_log_folder/" . $params->logFile;
        $this->warningFile = "./export/icar-import_log_folder/" . md5($params->logFile);
        $this->pathsValidation = $params->pathsValidation;
        $this->configFilePath = $params->config_file;
        $this->createLogFolder();
        parent::__construct($params, $runnerRef);
    }

    function execute($input)
    {
        $response = $this->callOAIService();

        $recordArray = [];
        $domRoot = [];
        $docString = '';
        try {
            foreach ($response as $r) {
                $dom = new DOMDocument();
                $content = str_replace("xmlns=\"\"", '', $r);
                //SE NON FUNZIONA QUALCOSA, ELIMINARE LA RIGHE SOTTOSTANTI
                $content = str_replace($this->prefix . ':', "", $content);
                $dom->loadXML(str_replace("xmlns=", "xmlns:faked=", $content));
                if ($this->checkIfIsreleasableRecord($dom)) {
                    $this->releaseRecord($dom);
                    continue;
                }
                $dom->preserveWhiteSpace = false;
                $root = $dom->getElementsByTagName($this->buildRootTag());
                if ($root->length) {
                    $domRoot[] = $root[0];
                }
            }

            foreach ($domRoot as $doc) {
                $recordBody = new DOMDocument();
                $docString = $doc->ownerDocument->saveXML($doc);
                $recordBody->loadXML(str_replace($this->prefix . ':', '', $docString));
                $recordArray[] = $recordBody;
            }
        } catch (Exception $ex) {
            if (!$this->suppressErrors) {
                throw $ex;
            }
        }
        $ret = new stdClass();
        $ret->argset = [];
        foreach ($recordArray as $r) {
            $obj = new StdClass();
            $rootNode = $r->documentElement;
            $obj->domElement = $rootNode;
            $ret->argset[] = $obj;
        }

        return $ret;
    }

    function validateInput($input)
    {
        return "";
    }

    function createLogFolder()
    {
        if (!file_exists('./export/icar-import_log_folder')) {
            mkdir('./export/icar-import_log_folder', 0777);
        }
    }

    function callOAIService()
    {
        $result = [];
        if (!$this->ids) {
            return $result;
        }
        $ids = explode('##', $this->ids);
        foreach ($ids as $id) {
            $curlSES = curl_init();
            curl_setopt($curlSES, CURLOPT_URL, $id);
            curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlSES, CURLOPT_HEADER, false);
            $curlResult = curl_exec($curlSES);
            $result[] = $curlResult;
            curl_close($curlSES);
        }
        return $result;
    }

    function checkIfIsreleasableRecord($dom)
    {
        $header = $dom->getElementsByTagName('header');
        if ($header->length) {
            $status = $header[0]->getAttribute('status');
            if (strtolower($status) == 'deleted') {
                return true;
            }
        }
        return false;
    }

    function releaseRecord($dom)
    {
        $requestElement = $dom->getElementsByTagName('request');
        if ($requestElement->length) {
            $url = $requestElement[0]->nodeValue . '?verb=GetRecord&identifier=' . $requestElement[0]->getAttribute('identifier') . '&metadataPrefix=' . $this->prefix;
        }
        $arcProxy = $this->getOrSetDefault("archiviProxy", pinax_ObjectFactory::createObject("archivi_models_proxy_ArchiviProxy"));
        $arcProxy->setRetryWithDraftOnInvalidate(true);
        $arcProxy->isImportProcess();
        $it = pinax_ObjectFactory::createModelIterator($this->detectModel())->where('oaiUrl', $url);
        foreach ($it as $ar) {
            $data = $ar->getRawData();
            $data->__model = $data->document_type;
            $data->__id = $data->document_id;
            $data->readOnly = "false";
            $data->oaiUrl = '';
            $data->externalID = '';
            $res = $arcProxy->save($data);
        }
        unset($arcProxy);
    }

    function detectModel()
    {
        if ($this->prefix == 'ead_icar') {
            return 'archivi.models.SchedaStrumentoRicerca';
        } else {
            return 'archivi.models.ProduttoreConservatore';
        }
    }

    function buildRootTag()
    {
        if (strpos($this->prefix, 'eac') !== false) {
            return str_replace('_icar', '-cpf', $this->prefix);
        }
        return str_replace('_icar', '', $this->prefix);
    }
}
