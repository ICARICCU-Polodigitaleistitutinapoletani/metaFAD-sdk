<?php

class metafad_mag_controllers_ImportProcedure extends metafad_common_controllers_Command
{
	public function execute($folder)
	{
		if(__Config::get('metafad.be.hasMag') === 'demo')
		{
			$this->logAndMessage(__T('Spiacente, questa funzionalità non è disponibile nella modalità DEMO.'), '', PNX_LOG_INFO);
			$this->changeAction('import');
		}
		$this->checkPermissionForBackend('edit');

		$instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
		$folder = __Request::exists('folder') ? __Request::get('folder') : false;
		$formType = __Request::exists('formType') ? __Request::get('formType') : null;
		$importMode = __Request::exists('importMode') ? __Request::get('importMode') : null;

		$overwrite = ($importMode === 'substitute') ? true : false;
		if (($folder!==false || isset($_FILES['fileToImport'])) && $instituteKey) {
			if (isset($_FILES['fileToImport'])) {
				$file = $_FILES['fileToImport'];
				$file_path = $file['tmp_name'];
				$file_name = $file['name'];

				$folder = __Config::get('metafad.importer.uploadFolder') . '/' . pathinfo($file['name'], PATHINFO_FILENAME);
				$zipFolder = $folder . '/' . $file['name'];
				@mkdir($folder);
				move_uploaded_file($file['tmp_name'], $zipFolder);

				$zip = new ZipArchive;
				if ($zip->open($zipFolder) === true) {
					$zip->extractTo($folder);
					$zip->close();
				}

				$fileList = glob($folder.'/*.xml');

				$this->log(
					sprintf('Inizio importazione MAG, file: %s, istituto: %s', $file_name, $instituteKey),
					PNX_LOG_DEBUG, PNX_LOG_INFO);
			} else {
				$this->log(
					sprintf('Inizio importazione MAG, cartella: %s, istituto: %s', $folder, $instituteKey),
					PNX_LOG_DEBUG, PNX_LOG_INFO);

				$fileList = glob(__Config::get('metafad.MAG.folder') . '/' . $folder . '/*.xml');
			}

			if (!count($fileList)) {
				$this->logAndMessage('Non ci sono file .xml in questa cartella "'.$folder.'"', '', PNX_LOG_ERROR);
			} else {
				$debug = '';

				try {
					// ora che ho la lista dei file (o il singolo file) procedo alla lettura e alla importazione
					$importService = pinax_ObjectFactory::createObject('metafad.mag.services.ImportMag',
						$this->application->retrieveService('metafad.mag.models.proxy.DocStruProxy'),
						pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia'.$debug, $instituteKey),
						$this->application->retrieveService('metafad.mag.services.Event'.$debug)
					);

					$importService->setImportOption($formType,$importMode);
					$importedFiles = $importService->importFolder($fileList, array('M', 'S'), false, false, $overwrite);

					$this->logAndMessage(sprintf('Importazione completata, %d MAG importati', $importedFiles));

					if (isset($_FILES['fileToImport'])) {
						pinax_helpers_Files::deleteDirectory($folder);
					}
				} catch (Exception $e) {
					$this->logAndMessage($e->getMessage(), '', true);
				}
			}
		} else {
			$this->logAndMessage('Parametri non corretti', '', true);
		}

		$this->changeAction('import');
	}
}
