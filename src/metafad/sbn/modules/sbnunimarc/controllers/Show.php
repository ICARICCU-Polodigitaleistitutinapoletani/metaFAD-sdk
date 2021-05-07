<?php

class metafad_sbn_modules_sbnunimarc_controllers_Show extends pinaxcms_contents_controllers_moduleEdit_Edit
{
    use metafad_sbn_modules_sbnunimarc_controllers_ShowTrait;

    public function execute($id)
    {
// TODO controllo ACL
        if ($id) {
            // read the module content
            $c = $this->view->getComponentById('__model');
            __Request::set('model', $c->getAttribute('value'));

            //*************** show con id **************

            /*$contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
            $data = $contentproxy->loadContent($id, $c->getAttribute('value'));*/

            //*************************************

            //*************** show con bid **************
            $data = array();

            $it = pinax_ObjectFactory::createModelIterator($c->getAttribute('value'));

            $ar = $it->where('id', $id)->orderBy('document_detail_modificationDate','DESC')->first();

            if ($ar) {
                $objData = $ar->getRawData();
                $data = json_decode(json_encode($objData), true);
            }
            //*************************************

            if ($data['hasParts']) {
                $this->view->getComponentById('textBoardLink')->setAttribute('enabled', 'true');
                $this->view->getComponentById('relatedBoardLink')->setAttribute('enabled', 'true');
                $this->view->getComponentById('relatedBoardGrid')->setAttribute('enabled', 'true');
            };

            $data['__id'] = $id;

            $inventoryComponent = $this->view->getComponentById('inventoryNumber');
            $inventoryComponentStrumag = $this->view->getComponentById('strumagInventoryNumber');
            $dataInventory = $inventoryComponent->getAttribute('data');
            $inventoryComponent->setAttribute('data',$dataInventory.';proxy_params={##inventory##:##'.str_replace("\"","'",json_encode($data['inventory'])).'##}');
            $inventoryComponentStrumag->setAttribute('data',$dataInventory.';proxy_params={##inventory##:##'.str_replace("\"","'",json_encode($data['inventory'])).'##}');

            $this->enableJSTab($data);

            $inventoryCollectionCopiesBE = array();

            foreach ($data as $key => $value) {
                if($key == 'linkedMedia' || $key == 'linkedInventoryMedia' || $key == 'linkedStruMag' || $key == 'linkedInventoryStrumag' || $key == 'ecommerceLicenses' || $key == 'visibility')
                {
                  continue;
                }
                if (is_array($value)) {
                    $objectHTML = $key . '_html';
                    $objectPlain = $key . '_plain';
                    if ($value[0]->$objectHTML) {
                        unset($value[0]->$objectPlain);
                    }
                }
                if ($this->view->getComponentById($key)) {
                    $count = 0;
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            if ($key == 'inventoryCollectionCopiesBE') {
                                if ($postBid = strstr($val, 'bid=')) {
                                    $tmp = explode("\n",strip_tags($postBid));
                                    $postBid = $tmp[0];
                                    $arrayInventory = explode("\n",$val);

                                    $inventoryCollectionCopiesBE[] = $this->processKardex($arrayInventory, $val);
                                }
                            } else if ($postBid = strstr($val, 'BID=')) {
                                $str = substr($postBid, 0, strpos($postBid, '">'));
                                $bid = str_replace('BID=', '', $str);
                                $url = __Link::makeURL('actionsMVC',
                                    array(
                                        'pageId' => 'metafad.sbn.unimarcSBN_popup',
                                        'title' => __T('PNX_RECORD_EDIT'),
                                        'action' => 'show', 'id' => $bid));
                                $data[$key][$count] = str_replace('href="${page}', 'class="rif" data-url="' . $url, $data[$key][$count]);
                                $data[$key][$count] = str_replace('?BID=' . $bid . '">', '">', $data[$key][$count]);
                            } else if ($postVid = strstr($val, 'VID=')) {
                                $str = substr($postVid, 0, strpos($postVid, '">'));
                                $vid = str_replace('VID=', '', $str);
                                $url = __Link::makeURL('actionsMVC',
                                    array(
                                        'pageId' => 'metafad.sbn.modules.authoritySBN_popup',
                                        'title' => __T('PNX_RECORD_EDIT'),
                                        'action' => 'show', 'id' => $vid));
                                $data[$key][$count] = str_replace('href="${page}', 'class="rif" data-url="' . $url, $data[$key][$count]);
                                $data[$key][$count] = str_replace('?VID=' . $vid . '">', '">', $data[$key][$count]);
                            }
                            $count++;
                        }
                        $this->view->getComponentById($key)->setAttribute('enabled', 'true');
                    }
                }
            }

            $data['inventoryCollectionCopiesBE'] = $inventoryCollectionCopiesBE ? : $data['inventoryCollectionCopiesBE'];

            if(!is_array($data['localization']))
            {
              $data['localization'] = array($data['localization']);
            }

            $localizations = array();

            foreach ($data['localization'] as $localization) {
                if ($localization == 'Biblioteca del Pio Monte della Misericordia') {
                    $localizations[] = 'Pio Monte della Misericordia';
                } else {
                    $localizations[] = $localization;
                }
            }

            $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
            $instituteName = metafad_usersAndPermissions_Common::getInstituteName();

            if ($instituteKey != '*' && !in_array(strtolower($instituteName), array_map('strtolower', $localizations))) {
                $c = $this->view->getComponentById('linkeMedia_tab');
                $c->setAttribute('visible', false);
            }

            $c = $this->view->getComponentById('relatedBoardGrid');
            $c->setAttribute('bid', $data['identificationCode'][0]);
            $this->view->getComponentById('editForm')->setData($data);
        }
    }



    protected function processKardex($arrayInventory, $val)
    {
        $instituteProxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.institutes.models.proxy.InstitutesProxy');
        $vo = $instituteProxy->getInstituteVoByKey(metafad_usersAndPermissions_Common::getInstituteKey());
        $institutePrefix = $vo->institute_prefix;

        $inventoryCollectionCopiesBE = htmlspecialchars_decode(str_replace('${kardexService}?', __Config::get('metafad.kardex.url'), $val));

        $kardexService = pinax_ObjectFactory::createObject('metafad.sbn.modules.sbnunimarc.services.KardexService');

        //Estraggo ogni link al kardex e lo elaboro
        $count = 0;
        foreach ($arrayInventory as $v) {
            if (strpos($v,'${kardexService}?') !== false) {
                $url = htmlspecialchars_decode(strip_tags(str_replace(array('${kardexService}?','Kardex'),array(__Config::get('metafad.kardex.url'),''),$v)));
                $file = $kardexService->getData($url);
                if ($file) {
                    $fileDecode = json_decode($file);
                    $text = ($fileDecode->kardexType->inventario[0]->documento->isbd) ? $fileDecode->kardexType->inventario[0]->documento->isbd : 'Mostra' ;
                } else {
                    $text = 'Titolo non disponibile' ;
                }

                $replace = '<span class="OpenGrid kardex" data-info="' . str_replace("</div>", "", $url ).'">' . $text . '</span>';

                preg_match('/biblioteca=(..)/', $url, $m);

                if ($m[1] == $institutePrefix) {
                    $replace .='<input class="ResynchKardex" type="button" value="Riscarica" data-url="'.$url.'"/>';
                }

                $inventoryCollectionCopiesBE = str_replace($url, $replace, $inventoryCollectionCopiesBE);
            }

            if ($count == 0) {
                $c = $this->view->getComponentById('kardexGrid');
                $c->setAttribute('kardexParam', $url);
            }

            $count++;
        }

        return $inventoryCollectionCopiesBE;
    }
    
}
