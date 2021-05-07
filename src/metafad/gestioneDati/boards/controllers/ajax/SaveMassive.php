<?php
class metafad_gestioneDati_boards_controllers_ajax_SaveMassive extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($data = null, $status = 'published')
    {
        $permissionToCheck = ($status == 'published') ? 'edit' : 'editDraft';
        $functionSave = ($status == 'published') ? 'save' : 'saveDraft';

        $result = $this->checkPermissionForBackend($permissionToCheck);
        if (is_array($result)) {
            return $result;
        }

        $pulsanti  = $this->getAssociativeArray(json_decode($data[1]), "campo");
        $fieldsets = $this->getAssociativeArray(json_decode($data[2]), "fieldset");
        $data = $data[0];
        $decodeData = json_decode($data);
        $fieldsToEmpty = array();

        foreach ($decodeData as $k => $d) {
            if (strpos($k, 'empty-') === 0) {
                if ($d == 'true') {
                    $fieldsToEmpty[] = str_replace('empty-', '', $k);
                }
            }
        }

        $draftPublished = 'PUBLISHED';
        $inverseDraftPublished = 'DRAFT';

        if ($decodeData->__selectedIds) {
            $ids = explode(",", str_replace('en-', '', $decodeData->__selectedIds));
        } else {
            $ids = explode("-", str_replace('en-', '', $decodeData->__id));
        }

        $model = $decodeData->__model;
        $arrayFieldToSkip = array('__id', '__model', 'isTemplate', 'popup', '__groupId', 'groupName');
        $fieldToSave = array();

        foreach ($decodeData as $key => $value) {
            if (!in_array($key, $arrayFieldToSkip)) {
                if ($value != null) {
                    $fieldToSave[$key] = $value;
                }
            }
        }

        $proxy = new metafad_gestioneDati_boards_models_proxy_ICCDProxy();

        foreach ($ids as $id) {
            $ar = pinax_ObjectFactory::createModel($model);
            $ar->load($id, $draftPublished);
            //Devo controllare che esista effettivamente la possibilità di salvare
            //il record nello stato selezionato... se non esiste una bozza devo salvare
            //in pubblica e viceversa...
            //Se un documento in quello stato non esiste:
            if (!$ar->document_id) {
                //Carico l'altra versione (che deve obbligatoriamente esistere)
                $ar->load($id, $inverseDraftPublished);
            }
            $data = $ar->getRawData();
            $this->modifycamposcheda($data, $fieldToSave, $fieldsets, $pulsanti);

            //Svuoto i campi indicati come da svuotare
            foreach ($fieldsToEmpty as $key) {
                $data->$key = null;
            }

            $data->__id = $ar->document_id;
            $data->__model = $model;

            $proxy->$functionSave($data);

            unset($ar);
        }
    }

    private function modifycamposcheda($data, $fieldToSave, $fieldsets, $pulsanti)
    {

        if ($data == null || $fieldToSave == null || $fieldsets == null || $pulsanti == null) return -1;


        foreach ($fieldToSave as $key => $value) {

            //Se è un array devo scendere di livello
            if (is_array($data->$key)) {

                $size = count($data->$key);
                $sizefieldset = count($value);

                //$value è un array vuoto
                if ($sizefieldset == 0) continue;

                //Se size = 0 significa che non c'è nessun elemento quindi sostituisco
                //Se lo stato del fieldset=edit ma ci sono più di un record nella scheda esistente , tutto viene sostituito
                if ($size == 0 || strcmp($fieldsets[$key][0]->stato, "aggiungi") == 0  || ($size > $sizefieldset && strcmp($fieldsets[$key][0]->stato, "edit") == 0)) {
                    $data->$key = $value;
                    continue;
                }

                //Scendo di livello
                for ($i = 0; $i < $size; $i++) {
                    $temp = $data->{$key};
                    $this->modifycamposcheda($temp[$i], $value[$i], $fieldsets, $pulsanti);
                }
            } else if ($data->$key == null) {

                $data->$key = $value;
            } else {

                //inserire un controllo

                $stato  = $pulsanti[$key][0]->stato;  //stato del fieldset { modifica,edit,aggiungi}
                $valore = $pulsanti[$key][0]->sostituisci; //valore da associare al campo
                $trova  = $pulsanti[$key][0]->trova;       //valore da ricercare per essere sostituito
                $pulsante = $pulsanti[$key][0]->pulsante;  //qual è il pulsante che abbiamo cliccato:( svuota,sostituisci,trovaesost)

                if (strcmp($stato, "aggiungi") == 0)
                    $data->$key = $value;
                else {

                    $val = "";
                    if (strcmp($pulsante, "svuota") == 0)
                        $data->$key = "";

                    else if (strcmp($pulsante, "sostituisci") == 0)
                        $data->$key = $value;

                    else if (strcmp($pulsante, "trovaesost") == 0)
                        $data->$key = str_replace($trova, $valore, $data->$key);
                }
            }
        }
    }

    /*
    *   Dato un array di stdclass restituisci un array associativo con una chiave che dipende
    *   dal campo $key che deve essere presente all'interno di un stdclass
    *
    **/
    private function getAssociativeArray($els, $key)
    {

        $temp = array();

        foreach ($els as $el) {


            if (array_key_exists($el->{$key}, $temp)) {

                array_push($temp[$el->{$key}], $el);
            } else {


                $var = array();

                array_push($var, $el);
                $temp[$el->{$key}] = $var;
            }
        }


        return $temp;
    }

}
