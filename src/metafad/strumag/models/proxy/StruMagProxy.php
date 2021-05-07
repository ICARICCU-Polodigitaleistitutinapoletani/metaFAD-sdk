<?php
set_time_limit (0);

class metafad_strumag_models_proxy_StruMagProxy extends PinaxObject
{

    protected $application;

    function __construct()
    {
        $this->application = pinax_ObjectValues::get('org.pinax', 'application');
    }

    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $it = pinax_ObjectFactory::createModelIterator('metafad.strumag.models.Model');
        $document = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');

        if (metafad_usersAndPermissions_Common::getInstituteKey() != '*'){
            $it->where("instituteKey", metafad_usersAndPermissions_Common::getInstituteKey());
        }

        if ($term != '') {
            if(is_numeric($term)){
                $it->where('document_id', $term);
            } else{
                $it->where('title', '%'.$term.'%', 'ILIKE');
            }
        }

        $result = array();
        $size = 0;
        foreach($it as $ar) {
            $decodeRelatedMag = json_decode($ar->MAG);
            $existsDocRel = $document->load($decodeRelatedMag->id);
            if(!$existsDocRel) {
                $result[] = array(
                    'id' => $ar->getId(),
                    'text' => $ar->title
                );

                if(++$size >= 25) break;
            }
        }

        return $result;
    }

    public function modify($id, $data)
    {
        if ($this->validate($data)) {

            $document = $this->createModel($id, 'metafad.strumag.models.Model');

            foreach ($data as $key => $value) {
                $document->$key = $value;
            }

            try {
                return $document->publish(null, null);
            } catch (pinax_validators_ValidationException $e) {
                return $e->getErrors();
            }
        } else {
            // TODO
        }
    }

    public function validate($data)
    {
        return true;
    }

    protected function createModel($id = null, $model)
    {
        $document = pinax_ObjectFactory::createModel($model);
        if ($id) {
            $document->load($id);
        }
        return $document;
    }

    public function delete($id, $detachMag=true)
    {
        $this->detachStruMag($id);

        $model = 'metafad.strumag.models.Model';
        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
        $content = $contentproxy->loadContent($id, $model);

        if ($detachMag) {
            $magProxy = pinax_ObjectFactory::createObject('metafad.mag.models.proxy.MagProxy');

            $idMag = json_decode($content['MAG'])->id;
            if ($idMag) {
                $doc = new stdClass();
                $doc->relatedStru = "";
                $magProxy->modify($idMag, $doc);
            }
        }

        $dam = pinax_ObjectFactory::createObject('metafad.dam.services.ImportMedia');
        $result = $dam->search($content['title']);

        if (!empty($result)) {
            // cancellazione container dal DAM
            $dam->deleteContainer($result[0]->id, true);
        }

        $models = array(
            'archivi.models.UnitaArchivistica',
            'archivi.models.UnitaDocumentaria',
            'metafad.sbn.modules.sbnunimarc.model.Model'
        );

        $contentproxy->delete($id, $model);

        $evt = array('type' => 'deleteRecord', 'data' => $id);
        $this->dispatchEvent($evt);
    }

    protected function detachStruMag($id)
    {
        $models = array(
            'archivi.models.UnitaArchivistica',
            'archivi.models.UnitaDocumentaria',
            'metafad.sbn.modules.sbnunimarc.model.Model'
        );

        foreach ($models as $model) {
            $it = pinax_ObjectFactory::createModelIterator($model)
                ->setOptions(array('type' => 'PUBLISHED_DRAFT'))
                ->where('linkedStruMag', $id);

            foreach ($it as $ar) {
                $ar->linkedStruMag = null;
                $ar->saveCurrentPublished();
            }
        }
    }
}