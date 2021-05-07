<?php
class metafad_mets_Module
{
    static function registerModule()
    {
      pinax_loadLocale('metafad.mets');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.mets';
        $moduleVO->name = 'METS';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.mets';
        $moduleVO->model = 'metafad.mets.models.Model';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '
        <pnx:Page parentId="teca" id="mets" value="{i18n:Gestione METS}" icon="fa fa-angle-double-right" adm:acl="*" adm:aclPageTypes="teca-mets,teca-metsimport,img_popup_mets,audio_popup_mets,video_popup_mets" select="*">
          <pnx:Page pageType="metafad.mets.views.Admin" id="teca-mets" value="{i18n:Mostra lista}" visible="true"/>
          <pnx:Page pageType="metafad.mets.views.AdminImport" id="teca-metsimport" value="{i18n:Importa}" visible="true" />
        </pnx:Page>
        <pnx:Page pageType="metafad.mets.views.ImgPopup" id="img_popup_mets" visible="true" parentId="" />
        <pnx:Page pageType="metafad.mets.views.AudioPopup" id="audio_popup_mets" visible="true" parentId="" />
        <pnx:Page pageType="metafad.mets.views.VideoPopup" id="video_popup_mets" visible="true" parentId="" />';
        $moduleVO->canDuplicated = false;

		if(__Config::get('metafad.be.hasMets'))
		{
			pinax_Modules::addModule($moduleVO);
		}

    }
}
