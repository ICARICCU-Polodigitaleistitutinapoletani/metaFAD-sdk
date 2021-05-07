<?php

class metafad_workflow_activities_models_proxy_ActivitiesProxy extends PinaxObject
{
    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $it = pinax_ObjectFactory::createModelIterator('metafad.workflow.activities.models.Model');

        if ($term != '') {
            $it->where('title', '%'.$term.'%', 'ILIKE');
        }

        $result = array();

        foreach($it as $ar) {
            $result[] = array(
                'id' => $ar->getId(),
                'text' => $ar->title,
            );
        }

        return $result;
    }
}