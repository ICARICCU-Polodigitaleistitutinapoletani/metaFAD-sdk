<?php
class metafad_common_helpers_TematismoHelper
{
    public function compareTematismi($old, $new)
    {
        if ($old == '') {
            $old = [];
        }
        if (is_null($old) && is_null($new)) {
            return true;
        }
        if (count($old) == 0 && is_null($new)) {
            return true;
        }
        if (count($old) !== count($new)) {
            return false;
        }
        $tematismiOld = [];
        $tematismiNew = [];
        foreach ($old as $o => $oldVal) {
            $tematismiOld[] = trim($oldVal->tematismoField);
        }
        foreach ($new as $n => $newVal) {
            $tematismiNew[] = trim($newVal->tematismoField);
        }
        foreach ($tematismiOld as $t) {
            if (!in_array($t, $tematismiNew)) {
                return false;
            }
        }
        foreach ($tematismiNew as $t) {
            if (!in_array($t, $tematismiOld)) {
                return false;
            }
        }
        return true;
    }
}
