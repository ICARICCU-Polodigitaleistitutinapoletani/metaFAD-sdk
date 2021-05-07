<?php
class metafad_thesaurus_Module
{
    static function registerModule()
    {
        $classPath = 'iccd';
        pinax_loadLocale($classPath);

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.thesaurus';
        $moduleVO->name = 'Dizionari';
        $moduleVO->description = 'Modulo creazione schede iccd';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = $classPath;
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="impostazioni-sistema" pageType="metafad.thesaurus.views.Admin" id="metafad.thesaurus" value="{i18n:'.$moduleVO->name.'}" icon="fa fa-angle-double-right no-rot" adm:acl="*" />';
        $moduleVO->canDuplicated = false;

		if(__Config::get('metafad.be.hasDictionaries'))
		{
        	pinax_Modules::addModule( $moduleVO );
		}
    }
}
