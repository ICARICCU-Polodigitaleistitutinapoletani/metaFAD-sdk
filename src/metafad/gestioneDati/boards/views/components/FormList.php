<?php
class metafad_gestioneDati_boards_views_components_FormList extends pinax_components_Component
{
    function init()
    {
        parent::init();
    }

    public function render($outputMode=NULL, $skipChilds=false)
    {
        $output = '<div id="'.$this->getAttribute('id').'" class="hide">';
        $ids = explode("-",__Request::get('id'));
        $model = __Request::get('model');
        $script = '<script> var statesArray = {';

        foreach ($ids as $id) {

          $ar = pinax_ObjectFactory::createModel($model);
          $ar->load($id, 'PUBLISHED_DRAFT');
          $record = pinax_ObjectFactory::createModel($model);
          if($ar->hasPublishedVersion())
          {
            $record->load($id,'PUBLISHED');
            $script .= ($ar->hasDraftVersion()) ? '\''.$id.'\':\'PUBLISHED/DRAFT\',' : '\''.$id.'\':\'PUBLISHED\',' ;
          }
          else if($ar->hasDraftVersion())
          {
            $record->load($id,'DRAFT');
            $script .= '\''.$id.'\':\'DRAFT\',';
          }
        }

        $script = rtrim($script,',') . '};</script>';
        $output .= '</div>' . $script;
        $this->addOutputCode($output);
    }
}
