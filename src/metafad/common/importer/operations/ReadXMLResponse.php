<?php

class metafad_common_importer_operations_ReadXMLResponse extends metafad_common_importer_operations_LinkedToRunner
{
    protected $url;
    protected $suppressErrors = false;
    protected $keepDefaultNS = false;
    protected $prefix;
    protected $logFile = "";
    protected $warningFile;
    protected $configFilePath;
    protected $pathsAndValueValidation = false;
    protected $pathsValidation;
    protected $eacIds;
    protected $sconsIds;
    protected $sdcIds;
    protected $idsRelations;
    protected $validationError;
    protected $type;

    function __construct(stdClass $params, metafad_common_importer_MainRunner $runnerRef)
    {
        $this->url = $params->url;
        $this->suppressErrors = $params->suppress === "true";
        $this->keepDefaultNS = $params->keepDefaultNS === "true";
        $this->pathsAndValueValidation = $params->pathsAndValueValidation == "true";
        $this->prefix = $params->prefix;
        $this->logFile = "./export/icar-import_log_folder/" . $params->logFile;
        $this->warningFile = "./export/icar-import_log_folder/" . md5($params->logFile);
        $this->pathsValidation = $params->pathsValidation;
        $this->configFilePath = $params->config_file;
        $this->type = $params->type;
        $this->mapRelations($params);
        $this->createLogFolder();
        parent::__construct($params, $runnerRef);
    }

    function execute($input)
    {
        $doms = [];
        $response = $this->callOAIService();
        try {
            foreach ($response as $r) {
                $dom = new DOMDocument();
                $content = str_replace("xmlns=\"\"", '', $r);
                //SE NON FUNZIONA QUALCOSA, ELIMINARE LA RIGHE SOTTOSTANTI
                $content = str_replace("ead_icar:", "", $content);
                $dom->loadXML(str_replace("xmlns=", "xmlns:faked=", $content));
                if ($this->checkIfIsreleasableRecord($dom)) {
                    $this->releaseRecord($dom);
                    continue;
                }
                $doms[] = $dom;
                // if ($this->pathsAndValueValidation && strpos($this->url, 'metadataPrefix=ead_icar') !== false) {
                //     $xpath = new DOMXPath($dom);
                //     $fieldsValidator = pinax_ObjectFactory::createObject('metafad_common_importer_operations_ValidateFields', $dom, $this->warningFile, $xpath, $this->pathsValidation, $this->configFilePath, false);
                //     if ($fieldsValidator->doValidation())
                //         $this->validationError = true;
                // }
                // if ($this->pathsAndValueValidation) {
                //     $this->validateRelations();
                //     if ($this->validationError) {
                //         $this->renameLogFile();
                //         throw pinax_ObjectFactory::createObject("metafad_common_importer_exceptions_RelValidationException");
                //     }
                // }
            }
            $recordArray = [];
            $domRoot = [];
            $docString = '';
            foreach ($doms as $sd) {
                $sd->preserveWhiteSpace = false;
                $domRoots = $sd->getElementsByTagName('ead');
                foreach ($domRoots as $d) {
                    $unitid = $d->getElementsByTagName('unitid');
                    if ($unitid->length) {
                        $domRoot[] = $d;
                    }
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
        $ret->document = [];
        foreach ($recordArray as $rec) {
            $ret->document[] = $rec;
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
        $urls = explode('##', $this->url);
        foreach ($urls as $url) {
            $curlSES = curl_init();
            curl_setopt($curlSES, CURLOPT_URL, $url);
            curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlSES, CURLOPT_HEADER, false);
            $curlResult[] = curl_exec($curlSES);
            curl_close($curlSES);
        }
        return $curlResult;
    }

    function mapRelations($params)
    {
        $this->idsRelations = [];
        if ($params->idsEac)
            $this->idsRelations['eac_icar'] = $params->idsEac;
        if ($params->idsScons)
            $this->idsRelations['scons_icar'] = $params->idsScons;
        if ($params->idsSdc)
            $this->idsRelations['ead_icar'] = $params->idsSdc;
    }



    function validateRelations()
    {
        //TODO correggere come in ReadXMLResponseRelations
        foreach ($this->idsRelations as $prefix => $values) {
            $ids = explode('##', $values);
            if (!$ids) {
                continue;
            }
            foreach ($ids as $id) {
                $curlSES = curl_init();
                curl_setopt($curlSES, CURLOPT_URL, $id);
                curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlSES, CURLOPT_HEADER, false);
                $response = curl_exec($curlSES);
                curl_close($curlSES);
                $content = str_replace("xmlns=\"\"", '', $response);
                //SE NON FUNZIONA QUALCOSA, ELIMINARE LA RIGHE SOTTOSTANTI
                $content = str_replace("$prefix:", "", $content);
                $dom = new DOMDocument();
                $dom->loadXML(str_replace("xmlns=", "xmlns:faked=", $content));

                $xpath = new DOMXPath($dom);
                $fieldsValidator = pinax_ObjectFactory::createObject('metafad_common_importer_operations_ValidateFields', $dom, $this->warningFile, $xpath, $this->pathsValidation, $this->configFilePath, false);
                if ($fieldsValidator->doValidation())
                    $this->validationError = true;
            }
        }
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
            $url = $requestElement[0]->nodeValue . '?verb=GetRecord&identifier=' . $requestElement[0]->getAttribute('identifier') . '&metadataPrefix=ead_icar';
        }
        $arcProxy = $this->getOrSetDefault("archiviProxy", pinax_ObjectFactory::createObject("archivi_models_proxy_ArchiviProxy"));
        $arcProxy->setRetryWithDraftOnInvalidate(true);
        $arcProxy->isImportProcess();
        $it = pinax_ObjectFactory::createModelIterator('archivi.models.ComplessoArchivistico')->where('oaiUrl', $url);
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

    function renameLogFile()
    {
        $warningFile = $this->warningFile . '.log';
        $newName = str_replace('.log', '_logError.log', $warningFile);
        rename($warningFile, $newName);
    }
}
