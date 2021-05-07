<?php
class metafad_usersAndPermissions_users_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.usersAndPermissions.users';
        $moduleVO->name = 'Utenti';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.usersAndPermissions.users';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule($moduleVO);
    }
}