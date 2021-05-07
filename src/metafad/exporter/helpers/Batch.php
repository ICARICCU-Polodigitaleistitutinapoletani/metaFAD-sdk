<?php
class metafad_exporter_helpers_Batch extends metafad_jobmanager_service_JobService
{
  public function run()
  {
    set_error_handler(array($this, 'errorHandler'));

    try {

      $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);

      $param = $this->params;

      $format = $param['format'];
      $arrayIds = $param['arrayIds'];
      $moduleName = $param['moduleName'];
      $autbib = $param['autbib'];
      $folderName = $param['foldername'];
      $damInstance = $param['damInstance'];
      $email = $param['email'];
      $title = $param['title'];
      $host = $param['host'];
      $fileConfig = $param['fileConfig'];
      $exportMets = $param['exportMets'];
      $exportType = $param['exportType'];

      metafad_usersAndPermissions_Common::setInstituteKey($damInstance);

      if (!preg_match('/scheda([a-z]+?)([0-9]{3})([a-z]*)/i', $moduleName, $matches))
        preg_match('/([a-z]+?)([0-9]{3})([a-z]*)/i', $moduleName, $matches);
      $TSK = strtoupper($matches[1]);
      $version = $matches[2];

      $moduleName = "Scheda" . $TSK . $version . ucfirst($matches[3]);

      $applicationPath = pinax_Paths::get('APPLICATION');
      //$export_path = $applicationPath . 'mediaArchive_iccd/exportICCD/';
      $export_path = pinax_Paths::get('ROOT') . 'export/';

      $modulePath = $applicationPath . 'classes/userModules/' . $moduleName . '/';

      if ($format == "trc") {

        $exporter = pinax_ObjectFactory::createObject('metafad.exporter.services.trcexporter.TRCExporter');
        $exporter->exportGroup($arrayIds, $folderName, $modulePath, $moduleName, $export_path, $autbib);
      } else if ($format == "iccdxml") {

        $exporter = pinax_ObjectFactory::createObject('metafad.exporter.services.xmlexporter.TrcExporterXML');
        $exporter->exportGroup($arrayIds, $modulePath, $moduleName, $export_path, $folderName, $autbib);
      } else if ($format == "mets") {

        $exporter = pinax_ObjectFactory::createObject('metafad.exporter.services.metsexporter.METSExporter');
        $exporter->METSExport($arrayIds, $damInstance, $folderName);
      } else if ($format == "ead3") {
        $exporter = pinax_ObjectFactory::createObject('metafad.exporter.services.ead3exporter.EAD3Exporter', $arrayIds, $folderName, $fileConfig, $exportMets, $exportType, $this->jobId);
        $validationFailed = $exporter->export();
      } else {
        $this->setMessage('Il job non è compatibile con il formato selezionato (' . $format . ')');
        $this->save();
        $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        $error = true;
      }

      if (!$error) {
        $this->updateProgress(100);
        if ($validationFailed) {
          $this->setMessage('Esportazione fallita: il documento prodotto non è valido secondo lo schema xsd. Scarica il report nel pacchetto zip.');
        } else {
          $this->setMessage('Esportazione schede eseguita');
        }
        $this->updateStatus(metafad_jobmanager_JobStatus::COMPLETED);
        $this->save();
        if ($email != '') metafad_exporter_helpers_ExportHelper::sendDownloadEmail($email, $title, new pinax_types_DateTime, $host, $format, $folderName);
      }
    } catch (Error $e) {
      $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
      $this->save();
    }
  }

  public function errorHandler()
  {
    $error = error_get_last();
    if ($error['type'] === E_ERROR) {
      $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
      $this->save();
      die;
    }
  }
}
