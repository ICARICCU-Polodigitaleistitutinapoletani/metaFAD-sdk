<?php
class metafad_solr_helpers_HasImageHelper extends PinaxObject
{
    public function hasImage($data, $type) {
    	if ($type == 'iccd' && property_exists($data, 'FTA') && $data->FTA) {
    		foreach($data->FTA as $k => $v) {
    			if ($v->{"FTA-image"}) {
    				return true;
    			}
    		}
    	} else if ($type == 'archive') {
    		if ($data->linkedStruMag || $data->mediaCollegati) {
    			return true;
    		}
    	}

    	return false;
    }
}
