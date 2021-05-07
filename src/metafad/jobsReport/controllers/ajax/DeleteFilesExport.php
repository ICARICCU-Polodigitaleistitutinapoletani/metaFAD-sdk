<?php
class metafad_jobsReport_controllers_ajax_DeleteFilesExport extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($id)
    {
        $ar = pinax_ObjectFactory::createModel('metafad.jobmanager.models.Job');
        if ($ar->load($id)) {
            $linkZip = unserialize($ar->job_params);
            unlink(pinax_Paths::get('ROOT') . 'export/exportIcar/' . $linkZip["foldername"] . '.zip');
        }
    }
}
