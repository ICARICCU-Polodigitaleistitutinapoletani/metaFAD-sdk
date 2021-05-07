<?php
class metafad_mag_views_components_MagRelation extends pinax_components_Component
{
  function init()
  {
    // define the custom attributes
    parent::init();
  }

  public function render($outputMode=NULL, $skipChilds=false)
  {
    $id = __Request::get('id');
    if($id)
    {
      $relation = pinax_ObjectFactory::createModel( 'metafad.mag.models.Model' );
      $relation->load($id, 'PUBLISHED_DRAFT');

      $output ='<ul class="entities">
      <div class="relationsDiv">';

      if($relation->BIB_dc_relation)
      {
        foreach ($relation->BIB_dc_relation as $r) {
          $link = __Link::makeUrl('linkEdit',array('pageId'=>'tecamag','id'=>$r->BIB_dc_relation_value->id));
          $output .= '<li>
          <h5>ID: '.$r->BIB_dc_relation_value->id.'</h5>
          <div class="relation-type">'.$r->BIB_dc_relation_value->text.'</div>
          <div class="actions">
          <a class="button-go" href="'.$link.'"><i class="fa fa-eye" aria-hidden="true"></i> Vedi</a>
          <a href="'.$link.'"><i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i> Vai</a>
          </div>
          </li>';
        }
        $output .= '</div></ul>';
      }
      else
      {
        $output .= 'Nessuna relazione per questo MAG.</div></ul>';
      }
    }

    $this->addOutputCode($output);
  }
}
