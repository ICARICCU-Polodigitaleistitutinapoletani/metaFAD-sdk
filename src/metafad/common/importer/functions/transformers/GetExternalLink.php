<?php

class metafad_common_importer_functions_transformers_GetExternalLink implements metafad_common_importer_functions_transformers_TransformerInterface
{
    private $model;
    private $pageId;

    public function __construct($params)
    {
        $this->model = $params->model;
        $this->pageId = $params->pageId;
    }

    private function transformSingle($externalID)
    {
        $proxy = pinax_ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
        $it = pinax_ObjectFactory::createModelIterator($this->model)
            ->where('externalID', $externalID);
        $link = new StdClass();
        if ($it->count() > 0) {
            $ar = $it->first();
            $link->id = $ar->getId();
            $link->text = $ar->_denominazione;
        }
        unset($proxy);
        return $link;
    }

    public function transformItems($array)
    {
        $ret = array();
        $array = array_filter($array, function ($a) {
            return $a;
        });
        foreach ($array as $item) {
            $externalID = $item->externalID;
            if (!is_null($externalID)) {
                $ret[] = $this->transformSingle($externalID);
            }
        }
        return $ret;
    }
}
