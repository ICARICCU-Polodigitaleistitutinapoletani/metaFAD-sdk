<?php
class metafad_usersAndPermissions_institutes_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.usersAndPermissions.institutes';
        $moduleVO->name = 'Istituti';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.usersAndPermissions.institutes';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule($moduleVO);
    }
}