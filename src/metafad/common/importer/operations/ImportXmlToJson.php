<?php

class metafad_common_importer_operations_ImportXmlToJson extends metafad_common_importer_operations_XmlToJson
{

    private $acronimoSistema;
    /**
     * metafad_common_importer_operations_ReadXML constructor.
     * Riceve una stdClass con:<br>
     * <ul>
     * <li>suppress = Ignora gli errori nella execute (facoltativo)</li>
     * <li>schemafile = Nome del file JSON da usare per la mappatura antecedente a quella specifica</li>
     * @param stdClass $params
     * @param metafad_common_importer_MainRunner $runnerRef
     * @throws Exception se params non è conforme a quanto scritto in questa descrizione
     */
    function __construct(stdClass $params, metafad_common_importer_MainRunner $runnerRef)
    {
        $this->acronimoSistema = $params->acronimoSistema;
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
        $this->schemaFile = $input->schemafile ? json_decode(file_get_contents($input->schemafile)) : $this->schemaFile;

        if ($this->schemaFile === null) {
            throw new Exception("Schema passato all'XmlToJson formattato male o nullo: " . json_last_error_msg());
        }
        $output = parent::execute($input);
        $output->data->acronimoSistema = $this->acronimoSistema;

        return $output;
    }

    function validateInput($input)
    {
        if (!is_a($input->domElement, "DOMNode")) {
            throw new Exception("Tipo dell'input.document errato, previsto: DOMNode, ricevuto: " .
                (is_object($input->domElement) ? get_class($input->domElement) : gettype($input->domElement)));
        }
    }
}