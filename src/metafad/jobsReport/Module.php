<?php
class metafad_jobsReport_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.jobsReport';
        $moduleVO->name = 'Rapporto import/export';
        $moduleVO->description = 'Modulo rapporto importazione / esportazione batch';
        $moduleVO->version = '1.0.0';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="impostazioni-sistema" pageType="metafad.jobsReport.views.Admin" id="metafad.jobsReport" value="{i18n:'.$moduleVO->name.'}" icon="fa fa-angle-double-right no-rot" adm:acl="*" />';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );
    }
}
