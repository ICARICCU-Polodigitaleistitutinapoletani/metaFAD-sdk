<?php
class metafad_common_importer_utilities_ImportFileLogger
{
    public $warningFile;

    public function __construct($warningFile)
    {
        $this->warningFile = $warningFile . '.log';
    }

    public function writeLogLine($type, $path, $msg, $node = null)
    {
        if (is_null($node)) {
            $line = "";
        } else {
            $line = $node->getLineNo();
        }
        if (!file_exists($this->warningFile)) {
            error_log("Rigo documento\tPath\tTipo\tMessaggio\n", 3, $this->warningFile);
        }
        error_log("$line\t$path\t$type\t$msg\n", 3, $this->warningFile);
    }
}
