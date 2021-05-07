<?php
class metafad_importer_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.importer';
        $moduleVO->name = 'Importatore';
        $moduleVO->description = 'Modulo importazione schede iccd';
        $moduleVO->version = '1.0.0';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="impostazioni-sistema" pageType="metafad.importer.views.Admin" id="metafad.importer" value="{i18n:'.$moduleVO->name.'}" icon="fa fa-angle-double-right no-rot" adm:acl="*" /><pnx:Page id="Import_preview" pageType="metafad.importer.views.AdminImportList" parentId="" visible="true" adm:acl="*" hide="true"/>';
        $moduleVO->canDuplicated = false;

		if(__Config::get('metafad.be.hasImport'))
		{
        	pinax_Modules::addModule( $moduleVO );
		}
    }
}
