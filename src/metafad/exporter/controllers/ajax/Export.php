<?php
class metafad_exporter_controllers_ajax_Export extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute()
    {
        $result = $this->checkPermissionForBackend('publish');
        if (is_array($result)) {
            return $result;
        }

        if(__Request::get("confirmExport")=="Esporta"){
            echo "export".__Request::get("id");

            $arrayIds=explode("-",__Request::get("id"));
            $moduleName=__Request::get("pageId");
            $format=__Request::get("format");
            $autbib=__Request::get("autbib");
            $damInstance=__Config::get("metafad.dam.instance");

            //Creazione cartella export
            $milliseconds = microtime(true) * 100;
            $foldername =  $milliseconds . "_" . $moduleName . "_" . $format;

            //Creazione del job di export
            $jobFactory = pinax_ObjectFactory::createObject('metafad.jobmanager.JobFactory');
            $jobFactory->createJob('metafad.exporter.helpers.Batch',
                                   array(
                                      'format' => $format,
                                      'arrayIds' => $arrayIds,
                                      'moduleName' => $moduleName,
                                      'autbib' => $autbib,
                                      'foldername' => str_replace(' ','_',$foldername),
                                      'damInstance' => $damInstance
                                    ),
                                   'Esportazione schede '.$format.' ('.count($arrayIds).')',
                                   'BACKGROUND');
            $url = pinax_helpers_Link::makeUrl( 'link', array( 'pageId' => 'metafad.jobsReport' ) );
            pinax_helpers_Navigation::gotoUrl($url);

          }

    }

}
