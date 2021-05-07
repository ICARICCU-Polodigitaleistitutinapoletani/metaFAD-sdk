<?php
class metafad_tei_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'tei';
        $moduleVO->name = 'TEI';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.tei';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '
<pnx:Page parentId="gestione-dati-bibliografico" id="tei-Manoscritto" pageType="metafad.tei.views.AdminManoscritto" value="{i18n:Manoscritti}" icon="" adm:acl="*"/>
<pnx:Page parentId="gestione-dati-bibliografico" id="tei-UnitaCodicologica" pageType="metafad.tei.views.AdminUnitaCodicologica" value="{i18n:Unità codicologica}" icon="" adm:acl="*" hide="true"/>
<pnx:Page parentId="gestione-dati-bibliografico" id="tei-UnitaTestuale" pageType="metafad.tei.views.AdminUnitaTestuale" value="{i18n:Unità testuale}" icon="" adm:acl="*" hide="true"/>
';
        $moduleVO->canDuplicated = false;
        $moduleVO->subPageTypes = array('tei-Manoscritto@Manoscritto', 'tei-UnitaCodicologica@Unità codicologica', 'tei-UnitaTestuale@Unità testuale');
        pinax_Modules::addModule( $moduleVO );
    }
}