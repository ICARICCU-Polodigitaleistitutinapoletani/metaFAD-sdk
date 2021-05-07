<?php

class metafad_workflow_activities_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.workflow.activities';
        $moduleVO->name = 'Attività';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.workflow.activities';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="processi" id="processi-definizione-attivita" pageType="metafad.workflow.activities.views.Admin" value="{i18n:Definizione attività}" icon="fa fa-angle-double-right" adm:acl="*"/>';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule($moduleVO);
    }
}