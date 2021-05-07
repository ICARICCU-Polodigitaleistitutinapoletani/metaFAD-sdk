<?php

class metafad_thesaurus_models_proxy_ThesaurusProxy extends PinaxObject
{
    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        if (property_exists($proxyParams, 'code')) {
            $showTipe = $this->getShowType($proxyParams->code);
        } else {
            $showTipe = 1;
        }
        $it = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusForms')
            ->load('findTerm', array('moduleId' => $proxyParams->module, 'fieldName' => ($proxyParams->fieldName) ?: $fieldName, 'level' => ($proxyParams->level)?:0));
        if ($proxyParams->parentKey && !$proxyParams->ignoreFilter) {
            $id = clone $it;
            $thesaurusId = $id->first()->thesaurusdetails_FK_thesaurus_id;
            $idParent = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.Details')
                ->where('thesaurusdetails_FK_thesaurus_id', $thesaurusId)
                ->where('thesaurusdetails_key', $proxyParams->parentKey)
                ->first()->thesaurusdetails_id;
            $it->where('thesaurusdetails_parent', $idParent);
        }

        if ($term != '') {
            $it->where('thesaurusdetails_value', '%' . $term . '%', 'ILIKE')
                ->limit(0, 250);
        } else {
            $it->limit(0, 250);
        }

        $hasAltro = false;
        $result = array();

        foreach ($it as $ar) {
            if (strtolower($ar->thesaurusdetails_key) === 'altro') {
                $altroKey = $ar->thesaurusdetails_key;
                $altroValue = $ar->thesaurusdetails_value;
                $hasAltro = true;
                continue;
            }
            if ($ar->thesaurusdetails_key == $ar->thesaurusdetails_value) {
                $text = $ar->thesaurusdetails_key;
            } elseif ($showTipe !== 0) {
                $text = $ar->thesaurusdetails_key . ' - ' . $ar->thesaurusdetails_value;
            } else {
                $text = $ar->thesaurusdetails_value;
            }

            $result[] = array(
                'id' => $ar->thesaurusdetails_key,
                'text' => $text
            );
        }

        if ($hasAltro) {
            $result[] = array(
                'id' => $altroKey,
                'text' => $altroValue
            );
        }

        return $result;
    }

    public function findOrCreate($name, $code)
    {
        $thesaurus = pinax_ObjectFactory::createModel('metafad.thesaurus.models.Thesaurus');
        if (!$thesaurus->find(array('thesaurus_code' => $code))) {
            $thesaurus->thesaurus_name = $name;
            $thesaurus->thesaurus_code = $code;
            $thesaurus->thesaurus_creationDate = new pinax_types_DateTime();
            $thesaurus->thesaurus_modificationDate = new pinax_types_DateTime();
            $thesaurus->save();
        }
    }

    //Il valore restituito da questa funzione stabilisce cosa sarà mostrato nella lista: solo l'etichetta (se "Mostra etichetta e codice"
    //è impostato su "No") oppure "codice - etichetta" (se "Mostra etichetta e codice" è impostato su "Sì"). Il campo "Mostra etichetta
    //e codice" è abilitato solo su ICAR.
    public function getShowType($code)
    {
        if (is_null($code)) {
            return 1;
        }
        $th = pinax_ObjectFactory::createModelIterator('metafad.thesaurus.models.Thesaurus')
            ->where('thesaurus_code', $code)->first();
        if ($th) {
            if ($th->fieldExists('thesaurus_keyValue')) {
                $result = $th->thesaurus_keyValue !== false ? 1 : 0;
                return $result;
            }
        }
        return 1;
    }
}
