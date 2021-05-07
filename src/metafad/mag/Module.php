<?php

class metafad_mag_Module
{
    static function registerModule()
    {
        pinax_loadLocale('metafad.mag');
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.mag';
        $moduleVO->name = 'MAG';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.mag';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '
        <pnx:Page id="export-mag" parentId="export" pageType="metafad.mag.views.AdminExport" value="{i18n:MAG}" icon="fa fa-angle-double-right" adm:acl="*"/>

        <pnx:Page parentId="teca" id="teca-MAG" value="{i18n:Gestione MAG}" icon="fa fa-angle-double-right" adm:acl="*" adm:aclPageTypes="tecaMAG,tecaMAGlist,tecaMAGimport,tecaMAGexport,img_popup,audio_popup,video_popup,doc_popup,ocr_popup,dis_popup,metadata_popup" select="*">
          <pnx:Page pageType="metafad.mag.views.Admin" id="tecaMAG" value="{i18n:Mostra lista}" visible="true"/>
          <pnx:Page pageType="metafad.mag.views.Admin" id="tecaMAGimport" value="{i18n:Importa}" visible="true" url="tecaMAG/import/"/>
        </pnx:Page>
        
        <pnx:Page pageType="metafad.mag.views.ImgPopup" id="img_popup" visible="true" parentId="" />
        <pnx:Page pageType="metafad.mag.views.AudioPopup" id="audio_popup" visible="true" parentId="" />
        <pnx:Page pageType="metafad.mag.views.VideoPopup" id="video_popup" visible="true" parentId="" />
        <pnx:Page pageType="metafad.mag.views.DocPopup" id="doc_popup" visible="true" parentId="" />
        <pnx:Page pageType="metafad.mag.views.OcrPopup" id="ocr_popup" visible="true" parentId="" />
        <pnx:Page pageType="metafad.mag.views.DisPopup" id="dis_popup" visible="true" parentId="" />
        <pnx:Page pageType="metafad.mag.views.MetadataPopup" id="metadata_popup" visible="true" parentId="" />
        ';
        $moduleVO->canDuplicated = false;

		if(__Config::get('metafad.be.hasMag'))
		{
			pinax_Modules::addModule($moduleVO);
		}
    }
}
