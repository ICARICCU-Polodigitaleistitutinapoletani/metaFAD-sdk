<?php
class metafad_importer_services_iccd_TRCFromFile extends metafad_importer_services_iccd_TRCBase
{
    private $dir;
    use metafad_common_importer_operations_ICCD_TRCTrait;
    
    function __construct($type, $version, $moduleName, $TRCFilePath, $TRCFile)
    {
        parent::__construct($type, $version, $moduleName);
        $this->read($TRCFilePath, $TRCFile);
    }


    public function read($dir, $trcFile)
    {
      $this->dir = $dir;

      $file = $dir . $trcFile;
      if (file_exists($file)) {
          $this->setRepeatablesAndWithChildren($this->struct);

          list($content, $result) = $this->getRightTrc($file);
          $result = explode("\n", $result);

          $this->records = $this->getTrcRecords($result);
          $this->slog(count($this->records)." Record letti dal file TRC");

          $content = file($file);
          $fl = $content[0];
          $type = rtrim(substr($fl,9,3)," ");

          return true;
      }

      return false;
  }
}
