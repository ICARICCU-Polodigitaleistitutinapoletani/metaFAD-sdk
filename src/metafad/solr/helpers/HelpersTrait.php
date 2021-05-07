<?php
trait metafad_solr_helpers_HelpersTrait{
    public function setValues($value, &$array, $allSubFields = null)
    {
        if (is_object($value)) {
            $value = (array)$value;
        }
        if (is_string($value) && $value != null) {
            $array[] = $value;
        } else if (is_array($value)) {
            if ($value[0]) {
                foreach ($value as $val) {
                    $keys = array_keys((array)$val);
                    if ($keys)
                        foreach ($keys as $keyVal) {
                            if ($val->$keyVal) {
                                if (is_string($val->$keyVal)) {
                                    $array[] = $val->$keyVal;
                                } else {
                                    $this->setValues($val->$keyVal, $array, $allSubFields);
                                }
                            }
                        }
                }
            }
        }
    }

    public function setValuesString($value, &$array, $allSubFields = null)
    {
        if (is_object($value)) {
            $value = (array)$value;
        }
        if (is_string($value) && $value != null) {
            $array .= $value . ' # ';
        } else if (is_array($value)) {
            if ($value[0]) {
                foreach ($value as $val) {
                    $keys = array_keys((array)$val);
                    if ($keys)
                        foreach ($keys as $keyVal) {
                            if ($val->$keyVal) {
                                if (is_string($val->$keyVal)) {
                                    $array -= $val->$keyVal . ' # ';
                                } else {
                                    $this->setValuesString($val->$keyVal, $array, $allSubFields);
                                }
                            }
                        }
                }
            }
            //caso particolare AUT, va ricostruito l'insieme dei valori
            else if ($value['id']) {
                $record = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
                if ($record->load($value['id'])) {
                    foreach ($record->getRawData() as $key => $value) {
                        if (in_array($key, $allSubFields)) {
                            $array .= $value . ' # ';
                        }
                    }
                }
            }
        }
    }

    public function getChildrenFlat($element)
    {
        $array = array();
        foreach ($element->children as $child) {
            if ($child->children) {
                $array[] = $child->name;
                $array[] = $this->getChildren($child);
            } else {
                $array[] = $child->name;
            }
        }
        return $array;
    }

    public function getChildren($element)
    {
        $array = array();
        foreach ($element->children as $child) {
            if ($child->children) {
                $array[$child->name] = $this->getChildren($child);
            } else {
                $array[$child->name] = array();
            }
        }
        return $array;
    }
}