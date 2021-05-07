<?php
class metafad_gestioneDati_boards_models_proxy_ModuleProxy extends metafad_common_models_proxy_SolrQueueProxy
{
    /**
     * @param $objData stdClass
     * @return array
     */
    public function saveDraft($objData)
    {
        if (!property_exists($objData, 'isValid')) {
            $objData->isValid = 0;
        }

        $result = $this->saveData($objData, true);

        return $result;
    }

    /**
     * @param $objData
     * @param bool $tryDraft
     * @return array
     */
    public function save($objData, $tryDraft = false)
    {
        if (!property_exists($objData, 'isValid')) {
            $objData->isValid = 0;
        }

        $result = $this->saveData($objData, false);

        if (key_exists("errors", $result) && $tryDraft){
            return $this->saveDraft($objData);
        }

        // Salvataggio su FE solo in caso di scheda PUBLISHED ed escludo i template da questo
        if (__Config::get('metafad.be.hasFE') == 'true' && !$objData->isTemplate) {
            $model = pinax_ObjectFactory::createModel($objData->__model);
            $solrModel = $model->getFESolrDocument();
            if ($solrModel['feMapping']) {
                $metaindice = pinax_ObjectFactory::createObject('metafad.solr.helpers.MetaindiceHelper');
                $metaindice->genericMapping($objData);
            }
        }

        return $result;
    }

    /**
     * @param $objData stdClass
     * @param $isDraft
     * @return array
     */
    protected function saveData($objData, $isDraft)
    {
        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
        $result = $contentproxy->saveContent($objData, __Config::get('pinaxcms.content.history'), $isDraft === true);

        if ($result['__id']) {
            $result = array('set' => $result);
        } else {
            return array('errors' => $result);
        }

        $objData->__id = $result['set']['__id'];
        $this->appendDocumentToData($objData);
        parent::sendDataToSolr($objData, array('commit' => true));

        return $result;
    }

    protected function createModel($id = null, $model)
    {
        $document = pinax_ObjectFactory::createModel($model);
        if ($id) {
            $document->load($id);
        }
        return $document;
    }

    public function delete($id = null, $model)
    {
        $evt = array('type' => 'deleteRecord', 'data' => $id);
        $this->dispatchEvent($evt);

        // necessario a cancellare anche da eventuale indice FE e metaindice
        $this->deleteFromFE($id);

        if ($id) {
            $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
            $contentproxy->delete($id, $model);
        }
    }

    public function deleteFromFE($id = null)
    {
        if (__Config::get('metafad.be.hasFE') == 'true') {
            $evt = array('type' => 'deleteRecord', 'data' => array('id'=>$id,'option' => array('url' => __Config::get('metafad.solr.metaindice.url'))));
            $this->dispatchEvent($evt);
        }
    }
}
