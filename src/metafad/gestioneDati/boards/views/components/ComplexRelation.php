<?php
class metafad_gestioneDati_boards_views_components_ComplexRelation extends pinax_components_Component
{
    function init()
    {
        // define the custom attributes
        parent::init();
    }

    public function render($outputMode=NULL, $skipChilds=false)
    {
        $id = __Request::get('id');
        $relation = pinax_ObjectFactory::createModelIterator( 'metafad.gestioneDati.boards.models.ComplexRelations' )
                    ->where('complex_relation_FK_document_id',$id)->first();

        $type = $relation->complex_relation_form;
        $mother = ($relation->complex_relation_level == '0') ? true : false;

        if($mother)
        {
          $output ='<ul class="entities">
                      <div class="title-relation">Schede figlie (rapporto madre-figlia)</div>
                       <div class="relationsDiv">';
          $it = pinax_ObjectFactory::createModelIterator( 'metafad.gestioneDati.boards.models.ComplexRelations' )
                ->where('complex_relation_rver',$relation->complex_relation_rver)
                ->where('complex_relation_level', '0','<>')
                ->where('complex_relation_form', $type);

          foreach ($it as $ar) {
            $tsk = pinax_Modules::getModule($ar->complex_relation_form)->iccdModuleType;
            $link = __Link::makeUrl('linkEdit',array('pageId'=>$ar->complex_relation_form,'id'=>$ar->complex_relation_FK_document_id));
            $output .= '<li>
                          <h5>'.$relation->complex_relation_rver.'</h5>
                          <div class="relation-type">TSK: '.$tsk.'</div>
                          <div class="relation-type">Livello: '.$ar->complex_relation_level.'</div>
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
          $ar = pinax_ObjectFactory::createModelIterator( 'metafad.gestioneDati.boards.models.ComplexRelations' )
                ->where('complex_relation_rver',$relation->complex_relation_rver)
                ->where('complex_relation_level', '0')->first();
          $output ='<ul class="entities">
                      <div class="title-relation">Scheda madre</div>
                       <div class="relationsDiv">';
          if($ar)
          {
            $link = __Link::makeUrl('linkEdit',array('pageId'=>$ar->complex_relation_form,'id'=>$ar->complex_relation_FK_document_id));
            $tsk = pinax_Modules::getModule($ar->complex_relation_form)->iccdModuleType;

            $output .= '<li>
                          <h5>'.$ar->complex_relation_rver.'</h5>
                          <div class="relation-type">TSK: '.$tsk.'</div>
                          <div class="relation-type">Livello: 0</div>
                          <div class="actions">
                            <a class="button-go" href="'.$link.'"><i class="fa fa-eye" aria-hidden="true"></i> Vedi</a>
                            <a href="'.$link.'"><i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i> Vai</a>
                          </div>
                        </li>';
          }
          $output .= '</div></ul>';
        }

        $this->addOutputCode($output);
    }


}
