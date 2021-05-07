<?php

class metafad_gestioneDati_massiveEdit_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.gestioneDati.massiveEdit';
        $moduleVO->name = 'massiveEdit';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.gestioneDati.massiveEdit';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="gestione-dati" id="massiveEdit" pageType="metafad.gestioneDati.massiveEdit.views.Admin" value="{i18n:Modifica Massiva}" icon="fa fa-angle-double-right no-rot" adm:acl="*" />';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule($moduleVO);
    }
}
