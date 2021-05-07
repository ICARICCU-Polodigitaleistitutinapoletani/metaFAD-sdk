<?php
class metafad_sbn_modules_sbnunimarc_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.sbn.modules.sbnunimarc';
        $moduleVO->name = __T('Record SOLR');
        $moduleVO->description = 'Dati SOLR';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.sbn.modules.sbnunimarc';
        $moduleVO->model = 'metafad.sbn.modules.sbnunimarc.model.Model';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '
<pnx:Page parentId="gestione-dati" id="gestione-dati-bibliografico" pageType="Empty" value="{i18n:Bibliografico}" icon="fa fa-angle-double-right"  adm:acl="*" adm:aclPageTypes="metafad.sbn.modules.sbnunimarc,metafad.sbn.unimarcSBN_popup" >
    <pnx:Page id="metafad.sbn.modules.sbnunimarc" pageType="metafad.sbn.modules.sbnunimarc.views.Admin" value="{i18n:SBN Unimarc}" icon="" />
</pnx:Page>';
        $moduleVO->siteMapAdmin .= '<pnx:Page pageType="metafad.sbn.modules.sbnunimarc.views.AdminPopup" id="metafad.sbn.unimarcSBN_popup" parentId=""/>';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );
    }
}
