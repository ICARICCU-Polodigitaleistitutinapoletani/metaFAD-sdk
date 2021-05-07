<?php

class metafad_exporter_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'export';
        $moduleVO->name = 'Export';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page id="export" pageType="" value="{i18n:Esporta}" icon="fa fa-download" adm:acl="*">
            <pnx:Page id="export/patrimonio" pageType="" value="{i18n:Patrimonio}" icon="fa fa-angle-double-right" adm:acl="*"/>
            <pnx:Page id="archive_export" pageType="" value="{i18n:Archivi}" icon="fa fa-angle-double-right" adm:acl="*">
                <pnx:Page id="archive_export_mets" pageType="archivi.views.AdminExport" value="{i18n:METS-SAN}" icon="fa fa-angle-right" adm:acl="*"/>
                <pnx:Page id="archive_export_ead3" pageType="archivi.views.AdminExportEAD3" value="{i18n:EAD3}" icon="fa fa-angle-right" adm:acl="*"/>
            </pnx:Page>
        </pnx:Page>';
        $moduleVO->canDuplicated = false;

		if(__Config::get('metafad.be.hasExport'))
		{
			pinax_Modules::addModule($moduleVO);
		}
    }
}
