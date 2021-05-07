<?php
class metafad_exporter_services_ead3exporter_Logger extends PinaxObject
{
    public function write($type, $idRecord, $message)
    {
        if ($type == 0) {
            error_log("$idRecord\tWARNING\t$message\n", 3, "./export/ead3/logFile.log");
        }
        if ($type == 1) {
            error_log("$idRecord\tERROR\t$message\n", 3, "./export/ead3/logFile.log");
        }
    }
}
