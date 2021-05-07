<?php
class metafad_exporter_services_ead3exporter_utilities_PathRebuilder extends PinaxObject
{
    public function rebuildProduttoreCronologiaPath($path, $isQualifica = null)
    {
        $path = str_replace(['ead:', './'], ['eac-cpf:', ''], $path);
        $entrySegments = ['datesingle', 'daterange', 'fromdate', 'standarddate', 'todate', 'notbefore', 'notafter'];
        $outputSegmets = ['date', 'dateRange', 'fromDate', 'standardDate', 'toDate', 'notBefore', 'notAfter'];
        $str = str_replace($entrySegments, $outputSegmets, $path);
        if ($isQualifica) {
            if (($pos = strpos($str, 'dateRange')) !== false) {
                $str = substr($str, 0, $pos + 9);
            }
        }
        return $str;
    }
}
