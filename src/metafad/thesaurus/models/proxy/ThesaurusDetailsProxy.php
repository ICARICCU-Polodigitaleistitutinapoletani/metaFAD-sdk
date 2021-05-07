<?php

class metafad_thesaurus_models_proxy_ThesaurusDetailsProxy extends PinaxObject
{
    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $it = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusDetails');
        if ($term != '') {
            $it->where('title', '%'.$term.'%', 'ILIKE');
        }
        if($proxyParams)
        {
          if($proxyParams->id)
          {
            $thesaurus = $proxyParams->id;
            $it->where('thesaurusdetails_FK_thesaurus_id',$thesaurus);
          }
          if($proxyParams->level)
          {
            $level = $proxyParams->level;
            $it->where('thesaurusdetails_level',(int)$level - 1);
          }
        }

        $result = array();

        foreach($it as $ar) {
            $result[] = array(
                'id' => $ar->thesaurusdetails_id,
                'text' => $ar->thesaurusdetails_value,
            );
        }
        return $result;
    }

    public function findTermById($id)
    {
      $it = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusDetails')
            ->where('thesaurusdetails_id',$id)->first();
      return $it;
    }
}
