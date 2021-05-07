<?php
class metafad_sbn_modules_authoritySBN_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.sbn.modules.authoritySBN';
        $moduleVO->name = __T('Record SOLR');
        $moduleVO->description = 'Dati AuthoritySBN';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.sbn.modules.authoritySBN';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page id="metafad.sbn.modules.authoritySBN" parentId="gestione-dati/authority/sbn" pageType="metafad.sbn.modules.authoritySBN.views.Admin" value="{i18n:SBN AUT}" adm:acl="*" icon="" />';
        $moduleVO->siteMapAdmin .= '<pnx:Page pageType="metafad.sbn.modules.authoritySBN.views.AdminPopup" id="metafad.sbn.modules.authoritySBN_popup" parentId=""/>';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );
    }
}
