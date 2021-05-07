<?php

class metafad_common_importer_operations_XmlToJson extends metafad_common_importer_operations_LinkedToRunner
{
    protected $suppressErrors = false;
    protected $schemaFile = null;
    protected $document = null;
    protected $configXp = null;
    protected $configNode = null;
    protected $overwrite = false;
    protected $logFile;
    /**
     * @var $xpath DOMXPath
     */
    protected $xpath = null;

    private $namespaces;

    /**
     * metafad_common_importer_operations_ReadXML constructor.
     * Riceve una stdClass con:<br>
     * <ul>
     * <li>suppress = Ignora gli errori nella execute (facoltativo)</li>
     * <li>schemafile = Nome del file JSON da usare per la mappatura</li>
     * </ul>
     * @param stdClass $params
     * @param metafad_common_importer_MainRunner $runnerRef
     * @throws Exception se params non è conforme a quanto scritto in questa descrizione
     */
    function __construct(stdClass $params, metafad_common_importer_MainRunner $runnerRef)
    {

        $this->suppressErrors = $params->suppress === "true";
        $filePath = pinax_findClassPath(str_replace('./application/classes/', '', $params->schemafile), false, false);
        if (is_null($filePath)) {
            $filePath = $params->schemafile;
        }
        $this->schemaFile = json_decode(file_get_contents($filePath));
        if (isset($params->config_file)) {
            $configDocument = new DOMDocument();
            if ($params->config_file) {
                $configDocument->load($params->config_file);
            } else {
                $conf = pinax_ObjectFactory::createObject('metafad_common_importer_utilities_GetConfig')->getConfig();
                $configDocument->loadXML($conf);
            }
            $this->configXp = new DOMXPath($configDocument);
            $this->configXp->registerNamespace("php", "http://php.net/xpath");
            $this->configXp->registerPHPFunctions('strtolower');
            $this->configNode = '/root/' . $params->config_node . '/';
        }

        if (isset($params->namespaces)) {
            $this->namespaces = $params->namespaces;
        }

        $this->overwrite = $params->overwrite == '1';
        parent::__construct($params, $runnerRef);
    }

    /**
     * Riceve una stdClass con:<br>
     * <ul>
     * <li>domElement = Oggetto DOMElement</li>
     * <li>schemafile = File JSON da usare invece di quello passatogli via parametro (facoltativo)</li>
     * </ul>
     * Restituisce una stdClass con:<br>
     * <ul>
     * <li>data = stdClass che rappresenta il nodo da salvare</li>
     * </ul>
     *
     * @param stdClass $input
     * @throws Exception se suppressErrors è false (sennò ignora l'eccezione)
     * @return stdClass contenente i dati da salvare (si raggiungono con la chiave "data")
     */
    function execute($input)
    {
        /**
         * @var $node DOMElement
         */
        $node = $input->domElement;
        $filePath = pinax_findClassPath(str_replace('./application/classes/', '', $input->schemafile), false, false);
        $this->schemaFile = $input->schemafile ? json_decode(file_get_contents($filePath)) : $this->schemaFile;
        if ($input->schemafile) {
            echo "";
        }

        if (!$this->schemaFile) {
            throw new Exception("Schema passato all'XmlToJson potrebbe essere formattato male: " . json_last_error_msg());
        }

        $this->document = new DOMDocument();
        $this->document->appendChild($this->document->importNode($node, true));
        $this->xpath = new DOMXPath($this->document);

        if ($this->namespaces) {
            foreach ($this->namespaces as $prefix => $namespaceURI) {
                $this->xpath->registerNamespace($prefix, $namespaceURI);
            }
        }

        $data = new stdClass();
        foreach ($this->schemaFile as $k => $v) {
            $data->$k = $this->processField($v);
        }

        return (object) array("data" => $data);
    }

    /**
     * @param $field stdClass
     * @param DOMNode $context
     * @return array|mixed
     */
    protected function processField($field, $context = null)
    {
        if ($field->institute) {
            if (metafad_usersAndPermissions_Common::getInstituteKey() != $field->institute) {
                return $field->type == 'composite' ? array() : null;
            }
        }
        if ($field->selectivePath) {
            if ($this->checkPath($field->selectivePath, $field->selectiveValue, @$field->operator)) {
                return $field->type == 'composite' ? array() : null;
            }
        }
        switch (strtolower($field->type ?: "")) {
            case "composite":
                return $this->processCompositeField($field, $context);
            case "constant":
                return $field->value;
            default:
                return $this->processSimpleField($field, $context);
        }
    }

    /**
     * @param $field stdClass
     * @param DOMNode $context
     * @return array|mixed
     */
    protected function processCompositeField($field, $context = null)
    {
        $block = $field->struct ?: new stdClass();
        $xpath = $field->xpath;
        $retLists = array();

        $xpath = !is_array($xpath) ? array($xpath) : $xpath;
        foreach ($xpath as $xp) { //Per ogni xPath dato
            $rets = array();

            //            set_error_handler(function() use ($xp) {echo "$xp non è un'espressione valida\n";}, E_WARNING);
            $list = $context ? $this->xpath->query($xp, $context) : $this->xpath->query($xp);
            //            restore_error_handler();

            foreach ($list as $item) { //Per ogni nodo che corrisponde a tale xPath
                $ret = new stdClass();
                foreach ($block as $k => $v) { //Per ogni campo previsto dal pezzo di JSON preso in input
                    $ret->$k = $this->processField($v, $item);
                }
                $rets[] = $ret;
            }
            $retLists[] = $rets;
        }

        return $this->dataProcessing($field, $retLists);
    }

    /**
     * @param $field stdClass
     * @param DOMNode $context
     * @return array|mixed
     */
    protected function processSimpleField($field, $context = null)
    {
        $xpath = $field->xpath;
        $media = $field->ismedia === true; //TODO: devo usarlo?
        $itemLists = array();
        $admitVoids = $field->admitVoids === true;
        $trim = strtolower($field->trim) ?: "simple";

        $xpath = !is_array($xpath) ? array($xpath) : $xpath;
        foreach ($xpath as $xp) { //Per ogni xPath dato
            $items = array();
            $list = $context ? $this->xpath->query($xp, $context) : $this->xpath->query($xp);
            foreach ($list as $item) { //Per ogni nodo che corrisponde a tale xPath
                $toAdd = $item->textContent;
                if ($trim == "simple") {
                    $toAdd = trim($toAdd);
                } else if ($trim == "advanced") {
                    $toAdd = preg_replace('/\s+/i', " ", trim($toAdd));
                }
                if ($admitVoids || $toAdd) {
                    $items[] = $toAdd;
                }
            }
            $itemLists[] = $items;
        }
        if ($field->default && !count($items)) {
            $item = $this->manageDefault($field);
            $itemLists[] = $item;
        }

        return $this->dataProcessing($field, $itemLists);
    }

    /**
     * @param $field
     * @param $itemLists
     * @return array|mixed
     */
    protected function dataProcessing($field, $itemLists)
    {
        if (property_exists($field, "value")) {
            return $field->value;
        }

        $repeat = $field->repeatable === true;
        /**
         * @var $joiner metafad_common_importer_functions_joiners_JoinerInterface
         */
        $joiner = $field->joiner ? pinax_ObjectFactory::createObject($field->joiner->classname, $field->joiner->params) : null;
        /**
         * @var $transformer metafad_common_importer_functions_transformers_TransformerInterface
         */
        $transformer = $field->transform ? pinax_ObjectFactory::createObject($field->transform->classname, $field->transform->params) : null;
        if (is_a($transformer, "metafad_common_importer_functions_transformers_CreateExternalLink")) {
            $transformer->setOverwrite($this->overwrite);
        }
        /**
         * @var $conflictSolver metafad_common_importer_functions_solvers_SolverInterface
         */
        $conflictSolver = $field->solver ? pinax_ObjectFactory::createObject($field->solver->classname, $field->solver->params) : null;

        //TODO: Creare un Joiner di default
        $joinedData = $joiner ? $joiner->joinArrays($itemLists) : array_reduce($itemLists, function ($a, $b) {
            return array_merge($a, $b);
        }, array());

        //TODO: Creare un Solver di default
        if (!$repeat && !$field->transform->escapeSolver) {
            $joinedData = $conflictSolver ? $conflictSolver->solveConflict($joinedData) : (count($joinedData) ? array($joinedData[0]) : array(null));
        }

        //TODO: Creare un Transformer di default
        $transformedData = $transformer ? $transformer->transformItems($joinedData) : $joinedData;

        if ($field->vocabulary) {
            $transformedData = $this->manageVocabulary($transformedData, $field);
        }

        return $repeat ? $transformedData : $transformedData[0];
    }

    function validateInput($input)
    {
        if (!is_a($input->domElement, "DOMNode")) {
            throw new Exception("Tipo dell'input.document errato, previsto: DOMNode, ricevuto: " .
                (is_object($input->domElement) ? get_class($input->domElement) : gettype($input->domElement)));
        }
    }

    function manageVocabulary($transformedData, $field)
    {
        $ret = array();
        foreach ($transformedData as $t) {
            if ($t) {
                $t = strtolower($t);
                $val = $this->configXp->query($this->configNode . 'vocabulary/' . $field->vocabulary . "/entry[php:functionString('strtolower', @value)=\"$t\"]/text()")[0]->textContent;
                if ($field->vocabularyAdvanced) {
                    $val = explode('##', $val)[$field->vocabularyPosition];
                }
                $ret[] = $val;
            }
        }
        return $ret;
    }

    function manageDefault($field)
    {
        $ret = array();
        $ret[] = $this->configXp->query($this->configNode . 'mapping/' . $field->default . "/text()")[0]->textContent;
        return $ret;
    }

    function checkPath($xpath, $value, $operator)
    {
        $list = $this->xpath->query($xpath);
        if ($list->length !== 1) {
            return true;
        }
        $val = $list[0]->textContent;
        foreach ($value as $v) {
            if (!$operator) {
                if ($val !== $v) {
                    return true;
                    break;
                }
            } elseif ($operator == '!=') {
                if ($val === $v) {
                    return true;
                    break;
                }
            }
        }
        return false;
    }
}
