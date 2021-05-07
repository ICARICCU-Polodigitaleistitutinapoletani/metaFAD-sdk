<?php

class metafad_tei_views_components_Tree  extends pinax_components_Component
{
    /**
     * Init
     *
     * @return  void
     * @access  public
     */
    public function init()
    {
        $this->defineAttribute('title', false, '{i18n:pinaxcms.Site Structure}',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('startId', false, 0,    COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('path', false, '',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('selectId', false, 0,    COMPONENT_TYPE_INTEGER);

        // call the superclass for validate the attributes
        parent::init();
    }


    public function process() {
        $this->_content =  new metafad_tei_views_components_TreeVO();
        $this->_content->title = $this->getAttribute('title');
        $this->_content->ajaxUrl = $this->getAjaxUrl().'&controllerName=metafad.tei.controllers.ajax.GetTree&';
        $this->_content->startId = $this->getAttribute('startId');
        $this->_content->path = $this->getAttribute('path');
        $this->_content->selectId = $this->getAttribute('selectId');
    }

    public function render($outputMode=NULL, $skipChilds=false) {
        parent::render($outputMode, $skipChilds);
        if (!pinax_ObjectValues::get('pinaxcms.js', 'jsTree', false))
        {
            pinax_ObjectValues::set('pinaxcms.js', 'jsTree', true);
            $this->addOutputCode( pinax_helpers_CSS::linkStaticCSSfile( 'jquery/fancytree/dist/skin-bootstrap/ui.fancytree.min.css' ) );
            $this->addOutputCode( pinax_helpers_JS::linkStaticJSfile( 'jquery/fancytree/dist/jquery.fancytree-all.js' ) );
        }
    }
}

class metafad_tei_views_components_TreeVO
{
    public $title;
    public $ajaxUrl;
    public $startId;
}

class metafad_tei_views_components_Tree_render extends pinax_components_render_Render
{
    function getDefaultSkin()
    {
        $skin = <<<EOD
<div id="treeview">
    <div id="treeview-title" tal:condition="Component/title">
        <h3 tal:content="Component/title"></h3>
    </div>
    <div id="treeview-inner">
        <div id="js-pinaxcmsSiteTree" tal:attributes="data-ajaxurl Component/ajaxUrl; data-start Component/startId; data-path Component/path; data-selectid Component/selectId"></div>
    </div>
</div>
EOD;
        return $skin;
    }
}
