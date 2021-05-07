<?php

class iiif_services_Manifest extends PinaxObject
{
    private $ids = [];
    private $sizes = [];
    private $titles = [];

    public function getManifest($uid, $data, $type, $page = null)
    {
        $manifest = new stdClass();
        $manifest->{'@context'} = 'http://iiif.io/api/presentation/2/context.json';
        $manifest->{'@id'} = __Routing::scriptUrl();
        $manifest->{'@type'} = 'sc:Manifest';

        $metadata = $this->getRecordMetadata($uid, $type);
        $manifest->label = $metadata->denominazione_titolo_ss ? current($metadata->denominazione_titolo_ss) : null;
        $manifest->description = $this->getDescription($metadata, $uid, $type);
        $manifest->attribution = 'Archivio Digitale';

        $canvases = [];

        foreach ($data->physicalSTRU->image as $image) {
            $this->ids[] = '"' . $image->id . '"';
            $this->titles[$image->id] = ($image->label) ?: $image->title;
        }

        $this->getInfos();

        foreach ($data->physicalSTRU->image as $image) {
            $canvases[] = $this->getCanvas($image);
        }

        if ($this->checkIfStructured($data->logicalSTRU)) {
            $structures = [];
            $range = new stdClass();
            $range->{'@id'} = 'range-0';
            $range->{'@type'} = 'sc:Range';
            $range->{'label'} = 'Contenuti';

            foreach ($data->logicalSTRU as $ls) {
                if ($ls->key == 'exclude') {
                    continue;
                }
                $range->ranges[] = 'range-' . $ls->key;
                $structures = array_merge($this->createStructure($ls, $data), $structures);
            }
            $structures[] = $range;
            $manifest->structures = array_reverse($structures);
        }

        $manifest->sequences = [];
        $sequence = new stdClass();
        $sequence->{'@id'} = 'sequence';
        $sequence->{'@type'} = 'sc:Sequence';
        $sequence->label = 'Sequenza';
        if($page) {
            $sequence->startCanvas = PNX_HOST . '/rest/metadata/' . $page . '/canvas/' . $page . '.json';
        }
        $sequence->canvases = $canvases;

        $manifest->sequences[] = $sequence;
        return $manifest;
    }

    public function getCanvasById($uid)
    {
        $canvas = $this->getCanvas($uid);
        $canvas->{'@context'} = 'http://iiif.io/api/presentation/2/context.json';
        return $canvas;
    }

    public function getCanvas($image)
    {
        $explodeUrl = explode('/', $image->url);
        $institute = $explodeUrl[5];
        $uuid = $institute . '@get@' . $image->id .'@original';
        $uuidThumbnail = $institute . '@get@' . $image->id . '@thumbnail';
        $imgUrl = iiif_services_Common::getImageWithSize($uuid);
        $size = ($this->sizes[$image->id]) ? $this->sizes[$image->id] : getimagesize($imgUrl);

        $canvas = new StdClass();
        $canvas->{'@id'} = PNX_HOST . '/rest/metadata/' . $image->id . '/canvas/' . $image->id . '.json';
        $canvas->{'@type'} = 'sc:Canvas';
        $canvas->width = $size[0];
        $canvas->height = $size[1];
        $canvas->images[] = $this->getAnnotation($image);
        $thumbnail = new stdClass();
        $thumbnail->{"@id"} = PNX_HOST . '/rest/iiif/' . $uuidThumbnail . '/full/full/0/default.jpg';
        $canvas->thumbnail = $thumbnail;

        $canvas->label = $this->titles[$image->id];

        return $canvas;
    }

    public function getAnnotationById($uid)
    {
        $ar = __ObjectFactory::createModel('iiif.models.Document');
        $ar->find(['document_uid' => $uid]);
        $annotation = $this->getAnnotation($ar);
        $annotation->{'@context'} = 'http://iiif.io/api/presentation/2/context.json';
        return $annotation;
    }

    public function getAnnotation($image)
    {
        $uri = PNX_HOST . '/rest/metadata/' . $image->id . '/annotation/' . $image->id . '.json';
        $annotation = new stdClass();
        $annotation->{'@id'} = $uri;
        $annotation->{'@type'} = "oa:Annotation";
        $annotation->motivation = 'sc:painting';
        $annotation->on = $uri;
        $annotation->resource = $this->getResource($image);
        return $annotation;
    }

    public function getResource($image)
    {
        $explodeUrl = explode('/', $image->url);
        $institute = $explodeUrl[5];
        $uuid = $institute . '@get@' . $image->id . '@original';

        $imgUrl = iiif_services_Common::getImageWithSize($uuid);
        $size = ($this->sizes[$image->id]) ? $this->sizes[$image->id] : getimagesize($imgUrl);

        $resource = new StdClass();
        $resource->{'@id'} = PNX_HOST . '/rest/iiif/' . $uuid . '/full/full/0/default.jpg';
        $resource->{'@type'} = 'dctypes:Image';
        $resource->{'@format'} = 'image/jpeg';
        $resource->width = $size[0];
        $resource->height = $size[1];
        $resource->service  = new StdClass();
        $resource->service->{"@context"} = 'http://iiif.io/api/image/2/context.json';
        $resource->service->{"@id"} = PNX_HOST . '/rest/iiif/' . $uuid;
        $resource->service->profile = 'http://iiif.io/api/image/2/level2.json';

        return $resource;
    }

    public function encode($uid)
    {
        return urlencode(urlencode($uid));
    }

    private function getInfos()
    {
        $size = sizeof($this->ids);
        $toCheck = $size;
        $start = 1;
        $end = ($size < 50) ? $size : 50;


        $intervals = [];
        while (true) {
            $intervals[] = [$start, $end];
            $start = $start + 50;
            $end = ($end + 50 > $size) ? $size : $end + 50;
            if ($end >= $size) {
                if ($start <= $size) {
                    $intervals[] = [$start, $size];
                }
                break;
            }
        }

        foreach ($intervals as $interval) {
            $url = __Config::get('metafad.dam.solr.url');
            if(implode(' OR ', array_slice($this->ids, $interval[0] - 1, 50)) != '')
            {
                $postBody = array(
                    'q' => 'id:(' . implode(' OR ', array_slice($this->ids, $interval[0] - 1, 50)) . ')',
                    'start' => 0,
                    'rows' => 9999,
                    'fl' => 'id,height_i,width_i,title_s_lower',
                    'wt' => 'json'
                );

                $request = __ObjectFactory::createObject('pinax.rest.core.RestRequest', $url . 'select?', 'POST', http_build_query($postBody));
                $request->setTimeout(1000);
                $request->setAcceptType('application/json');
                $request->execute();

                $result = json_decode($request->getResponseBody())->response->docs;
                if ($result) {
                    foreach ($result as $doc) {
                        $this->sizes[$doc->id] = [0 => $doc->width_i, 1 => $doc->height_i];
                    }
                }
            }
        }
    }
    private function getRecordMetadata($uid, $type)
    {
        $url = __Config::get('metafad.solr.' . $type . '.url');

        $postBody = array(
            'q' => 'id:"' . $uid . '"',
            'start' => 0,
            'rows' => 1,
            'wt' => 'json'
        );

        $request = __ObjectFactory::createObject('pinax.rest.core.RestRequest', $url . 'select?', 'POST', http_build_query($postBody));
        $request->setTimeout(1000);
        $request->setAcceptType('application/json');
        $request->execute();

        $result = json_decode($request->getResponseBody())->response->docs;
        if ($result) {
            foreach ($result as $doc) {
                return $doc;
            }
        }
    }

    private function getDescription($metadata, $uid, $type)
    {
        //TODO
        //Aggiungere e ordinare campi se richiesto
        $description = '';
        $fields = [
            'data_ss' => 'Data',
            'tipo_documento_s' => 'Tipo di documento',
            'responsabilita_principale_ss' => 'Responsabilità principale',
            'collocazione_ss' => 'Collocazione',
            'livello_superiore_ss' => 'Complesso',
            'segnatura_ss' => 'Segnature',
            'cronologia_datazione_ECT_s' => 'Estremi Cronologici'
        ];

        //Lingua, Luogo di pubblicazione, tipo documento, titolo, Responsabilità principale, Responsabilità secondaria, Data, Luogo, Note generali, Bibliografia, Soggetto, Collocazione
        foreach ($fields as $field => $label) {
            if ($metadata->$field) {
                if($field == 'cronologia_datazione_ECT_s')
                {
                    $metadata->$field = $this->revertData($metadata->$field);
                }
                if (is_array($metadata->$field)) {
                    $description .= '<b>' . $label  . '</b>' . ': ' . current($metadata->$field) . '<br/>';
                } else {
                    $description .= '<b>' . $label  . '</b>' . ': ' . $metadata->$field . '<br/>';
                }
            }
        }

        return $description;
    }

    public function revertData($value)
	{
		$dataArr = explode('-', $value);
		if (count($dataArr) > 1) {
			$data1 = trim($dataArr[0]);
			$data2 = trim($dataArr[1]);
			if ($data1 == $data2) {
				array_pop($dataArr);
			}
		}
		$res = '';
		foreach ($dataArr as $data) {
			$arr = explode('/', trim($data));
			$arr = array_reverse($arr);
			$res .= ' - ' . implode('/', $arr);
		}
		return trim($res, ' - ');
	}

    private function checkIfStructured($structure)
    {
        if ($structure == null) {
            return false;
        }
        if (sizeof($structure) > 1) {
            return true;
        } else if ($structure[0]->key != 'exclude') {
            return true;
        } else {
            return false;
        }
    }

    private function createStructure($ls, $data)
    {
        $structures = [];
        $childrenStructures = [];

        $range = new stdClass();
        $range->{'@id'} = 'range-' . $ls->key;
        $range->{'@type'} = 'sc:Range';
        $range->{'label'} = $ls->title;

        if ($ls->children) {
            $range->ranges = [];
            foreach ($ls->children as $c) {
                $range->ranges[] = 'range-' . $c->key;
                $childrenStructures = array_merge($childrenStructures, $this->createStructure($c, $data));
                foreach ($childrenStructures as $cs) {
                    foreach ($data->physicalSTRU->image as $image) {
                        if (str_replace('range-', '', $cs->{'@id'}) == $image->keyNode) {
                            $cs->canvases[] = PNX_HOST . '/rest/metadata/' . $image->id . '/canvas/' . $image->id . '.json';;
                        }
                    }
                }
            }
        } else {
            foreach ($data->physicalSTRU->image as $image) {
                if ($ls->key == $image->keyNode) {
                    $range->canvases[] = PNX_HOST . '/rest/metadata/' . $image->id . '/canvas/' . $image->id . '.json';;
                }
            }
        }

        $structures[] = $range;

        if ($childrenStructures) {
            $structures = array_merge($childrenStructures, $structures);
        }
        return $structures;
    }
}
