<?php

/**
 * Created by PhpStorm.
 * User: marco
 * Date: 28/11/16
 * Time: 12.17
 */
class metafad_common_importer_operations_ICCD_TRCFromFile extends metafad_common_importer_operations_LinkedToRunner
{
    protected $file = "";

    use metafad_common_importer_operations_ICCD_TRCTrait;
    /**
     * Si aspetta:
     * filename = nome del file da cui leggere i record.
     * metafad_common_importer_operations_ICCD_TRCFromFile constructor.
     * @param stdClass $params
     * @param metafad_common_importer_MainRunner $runnerRef
     */
    public function __construct(stdClass $params, metafad_common_importer_MainRunner $runnerRef)
    {
        $this->file = $params->filename;
        parent::__construct($params, $runnerRef);
    }

    /**
     * Restituisce:
     * trcrecords = record in formato trc, ancora da convertire in stdClass
     * @param stdClass $input
     * @return stdClass (trcrecords contiene i record in formato trc)
     * @throws Exception se il file non esiste
     */
    public function execute($input)
    {
        $file = $this->file;
        if (file_exists($file)) {

            list($content, $result) = $this->getRightTrc($file);
            $result = explode("\n", $result);
        } else {
            throw new Exception("File '$file' doesn't exist");
        }

        return (object)array("trcrecords" => $result);
    }

    public function validateInput($input)
    {
        //TODO non controllo nulla
    }
}