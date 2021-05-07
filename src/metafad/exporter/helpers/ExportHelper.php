<?php
class metafad_exporter_helpers_ExportHelper extends PinaxObject
{
  static function sendDownloadEmail($email, $title, $date, $host, $format, $folderName)
  {
    $emails = explode(",", $email);
    $subject = str_replace("{title}", $title, __T('subjectExport'));
    $strDowload = '';

    if ($format == "trc" || $format == "iccdxml") {
      $strDowload = $host . '/export/' . $folderName . '.zip';
    }
    if ($format == "mets") {
      $strDowload = $host . '/export/' . $folderName . '.xml';
    }
    $body = str_replace(array("{link}", "{date}"), array($strDowload, $date), __T('bodyExport'));
    foreach ($emails as $e) {
      $r = pinax_helpers_Mail::sendEmail(
        array('email' => $e, 'name' => 'Utente metafad'),
        array('email' => __Config::get('SMTP_EMAIL'), 'name' => __Config::get('SMTP_SENDER')),
        $subject,
        $body
      );
    }
  }
}
