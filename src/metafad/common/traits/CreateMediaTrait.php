<?php
trait metafad_common_traits_CreateMediaTrait
{
    public function exploreLogicalStruWithKey($stru, $key)
    {
        foreach ($stru as $k => $value) {
            if ($value->key == $key) {
                return $value;
            } else if ($value->children) {
                $v = $this->exploreLogicalStruWithKey($value->children, $key);
            }
        }
        return $v;
    }

    public function getElementsId($stru, &$idList)
    {
        foreach ($stru as $k => $value) {
            if ($value->key == 'exclude') {
                continue;
            }
            $idList[] = $value->key;
            if ($value->children) {
                $this->getElementsId($value->children, $idList);
            }
        }
    }
}
