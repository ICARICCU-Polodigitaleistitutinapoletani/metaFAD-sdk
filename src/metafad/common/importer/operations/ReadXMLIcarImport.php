<?php
define('XML_PARSE_BIG_LINES', 4194304);

/**
 * Class metafad_common_importer_operations_ReadXML
 * Legge l'XML indicato dal filename e tenta di restituire un DOMDocument
 */
class metafad_common_importer_operations_ReadXMLIcarImport extends metafad_common_importer_operations_LinkedToRunner
{
    protected $filename = "";
    protected $suppressErrors = false;
    protected $keepDefaultNS = false;
    protected $validateAgainstXSD = false;
    protected $validateRelations = false;
    protected $type;
    protected $prefix;
    protected $logFile = "";
    protected $warningFile;
    protected $configFilePath;
    protected $partialValidation = false;
    protected $onlyValidation = false;
    protected $pathsAndValueValidation = false;
    protected $pathsValidation;
    /**
     * metafad_common_importer_operations_ReadXML constructor.
     * Riceve una stdClass con:<br>
     * <ul>
     * <li>suppress = Ignora gli errori nella execute</li>
     * <li>filename = Filename dell'XML da caricare</li>
     * <li>keepDefaultNS = "true" se e solo se si vuole mantenere il namespace di default</li>
     * </ul>
     * @param stdClass $params
     * @param metafad_common_importer_MainRunner $runnerRef
     */
    function __construct(stdClass $params, metafad_common_importer_MainRunner $runnerRef)
    {
        $this->suppressErrors = $params->suppress === "true";
        $this->keepDefaultNS = $params->keepDefaultNS === "true";
        $this->filename = $params->filename ?: $this->filename;
        $this->validateAgainstXSD = $params->validateAgainstXSD == "true";
        $this->validateRelations = $params->validateRelations == "true";
        $this->partialValidation = $params->partialValidation === "1";
        $this->onlyValidation = $params->onlyValidation == "true";
        $this->pathsAndValueValidation = $params->pathsAndValueValidation == "true";
        $this->type = $params->type;
        $this->prefix = $params->prefix;
        $this->logFile = "./export/icar-import_log_folder/" . $params->logFile;
        $this->warningFile = "./export/icar-import_log_folder/" . md5($params->logFile);
        $this->pathsValidation = $params->pathsValidation;
        $params->logFile;
        $this->configFilePath = $params->config_file;
        $this->createLogFolder();
        parent::__construct($params, $runnerRef);
    }

    /**
     * Restituisce una stdClass con:<br>
     * <ul>
     * <li>document = Oggetto DOMDocument con il file caricato</li>
     * </ul>
     * @param stdClass $input
     * @throws Exception se suppressErrors è false (sennò ignora l'eccezione)
     * @return stdClass
     */
    function execute($input)
    {
        $dom = new DOMDocument();
        try {
            if (!$this->keepDefaultNS) { //Orribile workaround per evitare gli xmlns senza prefisso
                $content = str_replace("xmlns=\"\"", '', file_get_contents($this->filename));
                //SE NON FUNZIONA QUALCOSA, ELIMINARE LA RIGHE SOTTOSTANTI
                $content = str_replace(["ead:", "eac-cpf:", "scons:"], ["", "", ""], $content);
                $dom->loadXML(str_replace("xmlns=", "xmlns:faked=", $content), XML_PARSE_BIG_LINES);
                // if ($this->validateAgainstXSD) {
                //     libxml_use_internal_errors(true);
                //     $domValidate = new DOMDocument();
                //     $domValidate->load($this->filename);
                //     if (!$domValidate->schemaValidate('http://www.san.beniculturali.it/tracciato/icar-import.xsd')) {
                //         error_log("Sono stati rilevati i seguenti errori:\n\nLINEA\tMESSAGGIO\n", 3, $this->logFile);
                //         $this->libxml_display_errors();
                //         libxml_use_internal_errors(false);
                //         throw pinax_ObjectFactory::createObject("metafad_common_importer_exceptions_RelValidationException");
                //     }
                // }
                // if($this->validateRelations) {
                //     $relValidator = pinax_ObjectFactory::createObject("metafad_common_importer_operations_RelationsValidator", $dom, $this->logFile, $this->partialValidation);
                //     if($relValidator->validate()) {
                //         throw pinax_ObjectFactory::createObject("metafad_common_importer_exceptions_RelValidationException");
                //     }
                // }
                // if ($this->pathsAndValueValidation) {
                //     $xpath = new DOMXPath($dom);
                //     //$xpath->registerNamespace('icar-import', 'http://www.san.beniculturali.it/icar-import');
                //     $fieldsValidator = pinax_ObjectFactory::createObject('metafad_common_importer_operations_ValidateFields', $dom, $this->warningFile, $xpath, $this->pathsValidation, $this->configFilePath);
                //     if ($fieldsValidator->doValidation())
                //         throw pinax_ObjectFactory::createObject("metafad_common_importer_exceptions_RelValidationException");
                // }
                if ($this->onlyValidation) {
                    return;
                }
            } else {
                $dom->load($this->filename);
            }
            $dom->preserveWhiteSpace = false;

            $recordArray = [];
            $domRoot = [];
            $docString = '';
            switch ($this->type) {
                case 'ead3Hierarchic':
                    $domRoots = $dom->getElementsByTagName('ead');
                    foreach ($domRoots as $d) {
                        $unitid = $d->getElementsByTagName('unitid');
                        if ($unitid->length) {
                            $domRoot[] = $d;
                            //break 2;
                        }
                    }
                    break;
                case 'scons':
                    $domRoots = $dom->getElementsByTagName('scons');
                    foreach ($domRoots as $d) {
                        $domRoot[] = $d;
                    }
                    break;
                case 'eac-cpf':
                    $domRoots = $dom->getElementsByTagName('eac-cpf');
                    foreach ($domRoots as $d) {
                        $domRoot[] = $d;
                    }
                    break;
                case 'ead-strumenti':
                    $domRoots = $dom->getElementsByTagName('ead');
                    foreach ($domRoots as $d) {
                        $unitid = $d->getElementsByTagName('unitid');
                        if (!$unitid->length) {
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
        if ($this->type == 'ead3Hierarchic') {
            foreach ($recordArray as $rec) {
                $ret->document[] = $rec;
            }
        } else {
            foreach ($recordArray as $r) {
                $obj = new StdClass();
                $rootNode = $r->documentElement;
                $obj->domElement = $rootNode;
                $ret->argset[] = $obj;
            }
        }

        return $ret;
    }

    function validateInput($input)
    {
        return "";
    }

    function libxml_display_error($error)
    {
        $return = "<br/>\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                //$return .= "<b>Warning $error->code</b>: ";
                //break;
                $return = '';
                return;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }
        return $error->line . "\t" . trim($error->message) . "\n";
    }

    function libxml_display_errors()
    {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            //print $this->libxml_display_error($error);
            error_log($this->libxml_display_error($error), 3, $this->logFile);
        }
        libxml_clear_errors();
    }

    function createLogFolder() {
        if (!file_exists('./export/icar-import_log_folder')) {
            mkdir('./export/icar-import_log_folder', 0777);
        }
    }
}
