<?php
class metafad_mods_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'mods';
        $moduleVO->name = 'MODS';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.mods';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '
<pnx:Page parentId="gestione-dati-bibliografico" id="mods" pageType="metafad.mods.views.Admin" value="{i18n:MODS}" icon="" adm:acl="*"/>
';
        $moduleVO->canDuplicated = false;
        $moduleVO->hasDictionaries = true;

        pinax_Modules::addModule( $moduleVO );
    }
}