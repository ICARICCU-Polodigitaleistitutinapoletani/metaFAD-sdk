<?php
class metafad_uploader_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.uploader';
        $moduleVO->name = 'Uploader';
        $moduleVO->description = 'Modulo upload su filesystem';
        $moduleVO->version = '1.0.0';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="impostazioni-sistema" pageType="metafad.uploader.views.Admin" id="metafad.uploader" value="{i18n:'.$moduleVO->name.'}" icon="fa fa-angle-double-right no-rot" adm:acl="*" />';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );
    }
}
