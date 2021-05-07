<?php

class metafad_common_importer_functions_transformers_CreateExternalLink implements metafad_common_importer_functions_transformers_TransformerInterface
{
    private $model;
    private $pageId;
    private $callableFunc;
    private $skipIterator;
    private $overwrite;

    public function __construct($params)
    {
        $this->model = $params->model;
        $this->pageId = $params->pageId;
        $this->callableFunc = $params->function;
        $this->skipIterator = $params->skipIterator == true;
    }

    private function transformSingle($stdClass)
    {
        if (!is_object($stdClass)) {
            throw new Exception("Chiamata CreateExternalLink su una variabile che non contiene una stdClass");
        }
        $link = new StdClass();
        // if (isset($stdClass->externalID2)) {
        //     $stdClass = $this->extractLastExternalId($stdClass);
        // }
        $it = pinax_ObjectFactory::createModelIterator($this->model)
            ->where('externalID', $stdClass->externalID);
        if ($it->count() > 0) {
            if (!$this->overwrite && $this->model != 'archivi.models.SchedaBibliografica' && $this->model != 'archivi.models.FonteArchivistica') {
                //TODO risolvere questione doppio externalID degli strumenti. Sono davvero necessari?
                foreach ($it as $ar) {
                    $ar->externalID = '';
                    $id = $ar->save(null, false, 'PUBLISHED');
                }
            } else {
                $ar = $it->first();
                $link->id = $ar->getId();
                $link->text = $ar->_denominazione;
                return $link;
            }
        }
        if (!$this->overwrite) {
            $it = pinax_ObjectFactory::createModelIterator($this->model)
                ->where('externalID', $stdClass->externalID . '##current##');
            if ($it->count() > 0) {
                $ar = $it->first();
                $link->id = $ar->getId();
                $link->text = $ar->_denominazione;
                unset($it);
                return $link;
            }
        }
        unset($it);

        $proxy = pinax_ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
        $proxy->setUpdateConsProdBidirectional(false);
        $data = new StdClass();
        $data->__model = $this->model;
        $data->pageId = $this->pageId;
        //TODO Passare l'acronimo come variabile
        $data->acronimoSistema = 'ICAR';
        $data = $this->{$this->callableFunc}($data, $stdClass);
        $res = $proxy->save($data);
        unset($proxy);
        $link = new StdClass();
        $link->id = $res['set']['__id'];
        if ($den = $res['set']['document']->_denominazione) {
            $link->text = $den;
        } else {
            $link->text = $res['set']['document']->identificativo;
        }
        return $link;
    }

    public function transformItems($array)
    {
        $ret = array();
        $array = array_filter($array, function ($a) {
            return $a;
        });
        foreach ($array as $item) {
            $ret[] = $this->transformSingle($item);
        }
        return $ret;
    }

    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;
    }

    // private function extractLastExternalId($object)
    // {
    //     $stdClass = new StdClass();
    //     $nums = [];
    //     $properties = get_object_vars($object);
    //     foreach ($properties as $k => $p) {
    //         if (strpos($k, 'externalID') !== false && strlen($k) > 10) {
    //             $num = substr($k, 10);
    //             if (is_numeric($num)) {
    //                 $nums[] = (int)$num;
    //             }
    //         }
    //     }
    //     $max = max($nums);
    //     $stdClass->externalID = $properties["externalID$max"];
    //     return $stdClass;
    // }

    private function createFonteArchivistica($data, $stdClass)
    {
        $data->titolo = $stdClass->entry;
        $data->externalID = $stdClass->entry;
        if ($stdClass->href) {
            $obj = new StdClass();
            $obj->url = $stdClass->href;
            $data->riferimentiWeb[] = $obj;
        }
        return $data;
    }

    private function createProduttoreConservatore($data, $stdClass)
    {
        $data->tipologiaChoice = 'Ente';
        if (!$this->overwrite) {
            $data->externalID = $stdClass->externalID . '##current##';
        } else {
            $data->externalID = $stdClass->externalID;
        }
        return $data;
    }

    private function createSchedaBibliografica($data, $stdClass)
    {
        $data->titoloLibroORivista = $stdClass->entry;
        $data->externalID = $stdClass->entry;
        $data->tipologiaSpecifica = $stdClass->tipologiaSpecifica;
        $data->annoDiEdizione = $stdClass->annoDiEdizione;
        if ($stdClass->href) {
            if ($stdClass->type == 'BIBSBN') {
                $data->rifSBN_url = $stdClass->href;
                $data->identificativoBid = substr($stdClass->href, strrpos($stdClass->href, '/') + 1);
            } elseif ($stdClass->type == 'BIBURI' || $stdClass->type == 'BIBTEXT') {
                $data->url = $stdClass->href;
            }
        }
        return $data;
    }

    private function createStrumentoRicerca($data, $stdClass)
    {
        if (!$this->overwrite) {
            $data->externalID = $stdClass->externalID . '##current##';
        } else {
            $data->externalID = $stdClass->externalID;
        }
        $data->titoloNormalizzato = $stdClass->externalID2;
        if (isset($stdClass->externalID2)) {
            $data->externalID2 = $stdClass->externalID2;
        }
        return $data;
    }
}
