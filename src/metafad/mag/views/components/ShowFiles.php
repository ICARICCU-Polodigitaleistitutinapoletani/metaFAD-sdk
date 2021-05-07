<?php
class metafad_mag_views_components_ShowFiles extends pinax_components_Component
{
  function init()
  {
    // define the custom attributes
    $this->defineAttribute('type',     true,   '',         COMPONENT_TYPE_STRING);
    parent::init();
  }

  public function render($outputMode=NULL, $skipChilds=false)
  {
    if(__Request::get('id'))
    {
      $type = ucfirst($this->getAttribute('type'));
      $docStruProxy = $this->_application->retrieveService('metafad.mag.models.proxy.DocStruProxy');

      $rootId = $docStruProxy->getRootNodeByDocumentId(__Request::get('id'));

      if($rootId)
      {
        $it = pinax_ObjectFactory::createModelIterator('metafad.mag.models.'.$type)
              ->where('docstru_rootId',$rootId->docstru_id)
              ->where('docstru_type',$type);
        $count = 0;

        $dam = pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia');

        $output = '<div class="mediaContainer">';
        foreach ($it as $ar) {
          $thumbnail = $dam->streamUrl($ar->dam_media_id, 'thumbnail');

          if ($type == 'Img')
          {
            $img = '<span class="mediaContent">
                      <img class="mediaImg" src="'.$thumbnail.'"/>
                    </span>';
          }

          $output .= '<div class="singleMediaDiv">'.
                          $img
                        .'<span class="mediaContent">
                          <ul>
                            <li>'.$ar->nomenclature.'</li>
                            <li><b>Sequenza:</b> '.$ar->sequence_number.'</li>
                            <li><b>Nome file:</b> '.$ar->title.'</li>
                          </ul>
                        </span>
                        <span class="mediaContentButton">
                          <div class="groupButton pull-right deleteMediaButton" data-id="'.$ar->document_id.'"><i class="fa fa-times" aria-hidden="true"></i></div>
                          <div class="groupButton pull-right singleMediaButton" data-pageid="'.$ar->docstru_type.'_popup" data-id="'.$ar->document_id.'"><i class="fa fa-pencil" aria-hidden="true"></i></div>
                        </span>
                      </div>';
          $count++;
        }
        if($output == '<div class="mediaContainer">')
        {
          $output .= '<div class="groupMessage">Non esiste nessun media di questo genere.</div>';
        }
        $output .= '</div>';
      }
    }

    $this->addOutputCode($output);
  }
}