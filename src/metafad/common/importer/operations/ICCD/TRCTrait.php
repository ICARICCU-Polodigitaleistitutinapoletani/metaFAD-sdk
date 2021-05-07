<?php
trait metafad_common_importer_operations_ICCD_TRCTrait
{
    public function getRightTrc($f)
    {
        $file = (!is_array($f) ? file($f) : $f);

        $content = array();
        $result = "";

        $nl = "\r\n";

        $c = 0;

        for ($i = 0; $i < count($file); $i++) {
            if (preg_match('/^[A-Z]{2,5}:/', $file[$i]) > 0) {
                if (strpos($content[$c - 1], $nl) === false) {
                    $content[$c - 1] .= $nl;
                }

                $content[$c] = $file[$i];

                $c++;
            } elseif (trim($file[$i]) != "") {
                $content[$c - 1] = str_replace(array($nl, "\n"), array("", "", ""), $content[$c - 1] . substr($file[$i], 6));
                $c++;
            }
        }


        for ($i = 0; $i < count($content); $i++)
            $result .= $content[$i];

        $content[$c] = "\nCD:";

        return array($content, str_replace("\n\r", "", $result));
    }
}