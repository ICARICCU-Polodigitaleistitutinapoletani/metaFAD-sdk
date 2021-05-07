<?php
class metafad_dam_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.dam';
        $moduleVO->name = 'Metadati strutturati';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.dam';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="teca" id="teca-dam" pageType="metafad.dam.views.Admin" value="{i18n:DAM}" icon="fa fa-angle-double-right no-rot" adm:acl="*" />';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule($moduleVO);
    }
}