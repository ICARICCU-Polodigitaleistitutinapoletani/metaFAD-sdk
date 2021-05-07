<?php
class metafad_exporter_services_ead3exporter_XSDValidator extends PinaxObject
{
    public function validate($schemaPath, $document, $suppressPrint = false, $logPath = '', $customFormatter = false, $skipWarning = false)
    {
        libxml_use_internal_errors(true);
        if (!$document->schemaValidate($schemaPath)) {
            if (!$suppressPrint) {
                print "<b>DOMDocument::schemaValidate() Generated Errors!</b>";
            } elseif(!$customFormatter) {
                error_log("<b>DOMDocument::schemaValidate() Generated Errors!</b>", 3, $logPath);
            } else {
                error_log("Sono stati rilevati i seguenti errori:\nLINEA\tMESSAGGIO\n\n", 3, $logPath);
            }
            $this->libxml_display_errors($logPath, $suppressPrint, $customFormatter, $skipWarning);
            libxml_use_internal_errors(false);
            return true;
        }
        return false;
    }

    private function libxml_display_error($error, $customFormatter=false)
    {
        $return = "<br/>\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "<b>Warning $error->code</b>: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }
        if($customFormatter) {
            return $this->customFormatter($error);
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .=    " in <b>$error->file</b>";
        }
        $return .= " on line <b>$error->line</b>\n";

        return $return;
    }

    private function libxml_display_errors($logPath, $suppressPrint, $customFormatter, $skipWarning)
    {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            if($skipWarning && $error->level == 1) {
                continue;
            }
            if (!$suppressPrint) {
                print $this->libxml_display_error($error);
            } else {
                error_log($this->libxml_display_error($error, $customFormatter), 3, $logPath);
            }
        }
        libxml_clear_errors();
    }

    private function customFormatter($error) {
        return $error->line . "\t" . trim($error->message) . "\n";
    }
}
