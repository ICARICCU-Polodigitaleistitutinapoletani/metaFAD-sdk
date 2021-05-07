<?php
class metafad_gestioneDati_schedeSemplificate_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.gestioneDati.schedeSemplificate';
        $moduleVO->name = 'Schede semplificate';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.gestioneDati.schedeSemplificate';
        $moduleVO->model = 'SchedaF400.models.Model';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="impostazioni-sistema" id="metafad.gestioneDati.schedeSemplificate" pageType="metafad.gestioneDati.schedeSemplificate.views.Admin" value="{i18n:Schede semplificate}" icon="fa fa-angle-double-right no-rot" adm:acl="*" />';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );

    }
}
