<?php
class metafad_workflow_processes_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.workflow.processes';
        $moduleVO->name = 'Processi';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.workflow.processes';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page parentId="processi" id="processi-definizione-processi" pageType="metafad.workflow.processes.views.Admin" value="{i18n:Definizione processi}" icon="fa fa-angle-double-right" adm:acl="*"/>';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );
    }
}