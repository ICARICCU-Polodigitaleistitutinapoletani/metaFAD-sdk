<?php
class metafad_opac_Module
{
    static function registerModule()
    {
        pinax_loadLocale('metafad.opac');
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.opac.configRicerche';
        $moduleVO->name = 'OPAC';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.opac';
        $moduleVO->model = 'metafad.opac.models.Model';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page pageType="metafad.opac.views.Admin" icon="fa fa-angle-double-right no-rot" parentId="opac" id="metafad.opac.configRicerche" value="{i18n:Configurazione ricerche}" adm:acl="*"/>';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );

    }
}
