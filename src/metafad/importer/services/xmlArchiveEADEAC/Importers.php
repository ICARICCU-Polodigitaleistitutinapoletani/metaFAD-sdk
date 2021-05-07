<?php

/**
 * Created by PhpStorm.
 * User: marco
 * Date: 13/02/17
 * Time: 14.16
 */
class metafad_importer_services_xmlArchiveEADEAC_Importers
{
    /**
     * Importa gli EAD specificati nel file con la chiave di istituto specificata.
     * <br>
     * jsonMaps è un array con le seguenti chiavi obbligatorie:
     * <ol>
     *   <li>common => filepath per il mapping comune</li>
     *   <li>CA => filepath per il mapping delle CA</li>
     *   <li>UA => filepath per il mapping delle UA</li>
     *   <li>UD => filepath per il mapping delle UD</li>
     * </ol>
     * <br>
     * PER EAD3 si aggiungono le seguenti chiavi obbligatorie:
     * <ol>
     *   <li>common => filepath per il mapping comune</li>
     *   <li>SCONS => filepath per il mapping dei soggetti conservatori</li>
     *   <li>EAC-CPF => filepath per il mapping dei soggetti produttori</li>
     *   <li>EAD-Strumenti => filepath per il mapping degli strummenti di ricerca</li>
     *   <li>DAO => filepath per il mappare gli href delle immagini</li>
     * </ol>
     * @param $filePath string Nome del file da importare
     * @param string $instituteKey Chiave dell'istituto a cui apparterrà l'intero fondo
     * @param null|array $jsonMaps Se non specificato, verrà usato un array di default
     * @param pinax_log_LogBase $logger
     * @param bool $createWrapperFondo Default false: crea un fondo che conterrà l'importazione
     * @param bool $ead3 Default false: indica se si tratta di un import ead3
     */
    public static function importEAD($filePath, $instituteKey = "societa-napoletana-di-storia-patria", $jsonMaps = null, $logger = null, $createWrapperFondo = false, $ead3 = false, $configFile = '', $logFile = '', $partialValidation = false, $overwriteScheda = false, $onlyValidation = false, $recordId = null, $onlyRecord = false, $onlyMedia = false)
    {
        $neededKeys = array("common", "CA", "UA", "UD");

        if (!$ead3) {
            $jsonMaps = $jsonMaps ?: array(
                "common" => __DIR__ . "/jsonSchemas/xDams/_commonArchive.json",
                "CA" => __DIR__ . "/jsonSchemas/xDams/ICAR_ca_schema.json",
                "UA" => __DIR__ . "/jsonSchemas/xDams/ICAR_ua_schema.json",
                "UD" => __DIR__ . "/jsonSchemas/xDams/ICAR_ud_schema.json"
            );
        } else {
            $jsonMaps = self::configureJsonMaps($jsonMaps);
        }

        array_map(function ($key) use ($jsonMaps) {
            if (!key_exists($key, $jsonMaps)) throw new Exception("La chiave $key non è stata specificata per la creazione della pipeline");
        }, $neededKeys);

        if ($createWrapperFondo) {
            $link = metafad_importer_services_xmlArchiveEADEAC_utils_Generator::generateFondo($instituteKey);
        } else {
            $link = array("id" => 0, "text" => "");
        }
        //TODO settare acronimo da Config
        if (!$ead3) {
            $pipeline = metafad_importer_services_xmlArchiveEADEAC_utils_PipelineProvider::getEADPipeline($filePath, $instituteKey, $jsonMaps, $link['id'], $link['text']);
        } else {
            $pipeline = metafad_importer_services_xmlArchiveEADEAC_utils_PipelineProvider::getEAD3Pipeline($filePath, $instituteKey, $jsonMaps, $link['id'], $link['text'], 'ICAR', $configFile, $logFile, $partialValidation, $overwriteScheda, $onlyValidation, $recordId, $onlyRecord, $onlyMedia);
        }
        self::executeImport($pipeline, $logger);
    }

    public static function importSIASSIUSA($url, $instituteKey, $logger, $type, $idRelations, $configFile, $logFile, $recordId, $jsonMaps = null)
    {
        $neededKeys = array("common", "CA", "UA", "UD");
        $jsonMaps = self::configureJsonMaps($jsonMaps);

        array_map(function ($key) use ($jsonMaps) {
            if (!key_exists($key, $jsonMaps)) throw new Exception("La chiave $key non è stata specificata per la creazione della pipeline");
        }, $neededKeys);

        //TODO settare acronimo da Config
        $pipeline = metafad_importer_services_xmlArchiveEADEAC_utils_PipelineProvider::getSiasSiusaPipeline($url, $instituteKey, $jsonMaps, 'ICAR', $configFile, $logFile, $type, $idRelations, $recordId);

        self::executeImport($pipeline, $logger);
    }

    private static function configureJsonMaps($jsonMaps)
    {
        $jsonMaps = $jsonMaps ?: array(
            "common" => __DIR__ . "/jsonSchemas/ead3/_commonArchive.json",
            "CA" => __DIR__ . "/jsonSchemas/ead3/ICAR_ca_schema.json",
            "UA" => __DIR__ . "/jsonSchemas/ead3/ICAR_ua_schema.json",
            "UD" => __DIR__ . "/jsonSchemas/ead3/ICAR_ud_schema.json",
            "SCONS" => __DIR__ . "/jsonSchemas/produttoriConservatori/ICAR_conservatori_schema.json",
            "EAC-CPF" => __DIR__ . "/jsonSchemas/produttoriConservatori/ICAR_produttori_schema.json",
            "EAD-Strumenti" => __DIR__ . "/jsonSchemas/strumentiRicerca/ICAR_strumenti_schema.json",
            "DAO" => __DIR__ . "/jsonSchemas/ead3/dao.json",
            "pathsValidation" => __DIR__ . "/jsonSchemas/ead3/pathsValidation.json"
        );

        return $jsonMaps;
    }


    /**
     * Importa gli EAC specificati nel file con la chiave di istituto specificata.
     * <br>
     * jsonMaps è un array con le seguenti chiavi obbligatorie:
     * <ol>
     *   <li>entitaPersona => filepath per il mapping delle entità di tipo Persona</li>
     *   <li>entitaFamiglia => filepath per il mapping delle entità di tipo Famiglia</li>
     *   <li>entitaEnte => filepath per il mapping delle entità di tipo Ente</li>
     *   <li>antroponimo => filepath per il mapping degli antroponimi</li>
     *   <li>ente => filepath per il mapping degli enti (voci d'indice)</li>
     * </ol>
     * @param $filePath
     * @param string $instituteKey
     * @param null|array $jsonMaps
     * @param pinax_log_LogBase $logger
     */
    public static function importEAC($filePath, $instituteKey = "societa-napoletana-di-storia-patria", $jsonMaps = null, $logger = null)
    {
        $neededKeys = array("entitaPersona", "entitaFamiglia", "entitaEnte", "antroponimo", "ente", "void");

        $jsonMaps = $jsonMaps ?: array(
            "entitaPersona" => __DIR__ . "/jsonSchemas/xDams/persona_schema.json",
            "entitaFamiglia" => __DIR__ . "/jsonSchemas/xDams/famiglia_schema.json",
            "entitaEnte" => __DIR__ . "/jsonSchemas/xDams/ente_schema.json",
            "antroponimo" => __DIR__ . "/jsonSchemas/xDams/antroponimo_schema.json",
            "ente" => __DIR__ . "/jsonSchemas/xDams/enteVI_schema.json",
            "void" => __DIR__ . "/jsonSchemas/xDams/void.json"
        );

        array_map(function ($key) use ($jsonMaps) {
            if (!key_exists($key, $jsonMaps)) throw new Exception("La chiave $key non è stata specificata per la creazione della pipeline");
        }, $neededKeys);

        $pipeline = metafad_importer_services_xmlArchiveEADEAC_utils_PipelineProvider::getEACPipeline($filePath, $instituteKey, $jsonMaps);

        self::executeImport($pipeline, $logger);
    }

    private static function fillSingleOpParams($opStdClass, $opName, $paramsToMerge, $overwrite = false)
    {
        if (!is_object($opStdClass)) {
            return;
        }
        foreach ($opStdClass as $k => $v) {
            if ($k == "obj" && $v == $opName) {
                $opStdClass->params = $opStdClass->params ?: new stdClass();
                foreach ($paramsToMerge as $key => $value) {
                    $opStdClass->params->$key = $overwrite ? $value : ($opStdClass->params->$key ?: $value);
                }
            } else if (!is_array($v)) {
                self::fillSingleOpParams($v, $opName, $paramsToMerge);
            } else {
                foreach ($v as $obj) {
                    self::fillSingleOpParams($obj, $opName, $paramsToMerge);
                }
            }
        }
    }

    private static function fillOperationParams($jsonPipeline, $opName, $paramsToMerge, $overwrite = false)
    {
        $pipeline = json_decode($jsonPipeline);

        if ($pipeline === null) {
            throw new Exception("La stringa pipeline ha qualche problema nella decodifica da JSON: " . json_last_error_msg());
        }

        foreach ($pipeline as $k => $v) {
            self::fillSingleOpParams($v, $opName, $paramsToMerge, $overwrite);
        }

        return $pipeline;
    }

    /**
     * @param $pipeline
     * @param pinax_log_LogBase $logger
     * @throws Exception
     */
    private static function executeImport($pipeline, $logger = null)
    {
        $gotException = false;

        /**
         * @var metafad_common_importer_MainRunner $runner
         */
        $runner = pinax_ObjectFactory::createObject("metafad.common.importer.MainRunner");
        try {
            $params = new stdClass();
            $params->logger = $logger;
            $ret = $runner->executeFromStdClasses(self::fillOperationParams($pipeline, "metafad_common_importer_operations_LogInput", $params));
        } catch (Exception $ex) {
            $ret = metafad_common_helpers_ImporterCommons::getThrowableString($ex);
            if (!is_a($ex, "metafad_common_importer_exceptions_RelValidationException")) {
                $gotException = true;
            }
        }
        if ($logger && $gotException) {
            $logger->debug($ret);
        } else if ($gotException) {
            throw new Exception("Errore riportato durante l'esecuzione: \r\n" . $ret);
        }
    }
}
