<?php
class metafad_sbn_modules_sbnunimarc_controllers_ajax_AutoComplete extends metafad_common_controllers_ajax_AutoComplete
{
    function execute($instituteKey = null, $model = null, $filters = null, $fieldName = null, $term = null)
    {
        return parent::execute($instituteKey, $model, $filters, $fieldName, $term); 
    }

    protected function buildQuery($instituteKey, $model, $filters, $fieldName, $term)
    {
        $docType = ($model !== 'metafad.sbn.modules.authoritySBN.model.Model') ? 'unimarcSBN' : 'authoritySBN';

        $q = array(
            'docType_s:'. $docType,
            $fieldName.':*'.$term.'*',
        );

        if ($filters) {
            foreach ($filters as $filter) {
                if ($filter['type'] == 'date' || $filter['type'] == 'dateCentury') {
                    $f = explode(',', $filter['name']);
                    $v = $filter['value'];

                    if ($filter['type'] = 'dateCentury') {
                        $romanService = pinax_ObjectFactory::createObject('metafad.common.helpers.RomanService');
                        $v[0] = $romanService->romanToInteger($v[0]);
                        $v[1] = $romanService->romanToInteger($v[1]);
                    }

                    if ($v[0]) {
                        $q[] = $f[0] . ':['.sprintf('%04d', $v[0]).' TO *]';
                    }

                    if ($v[1]) {
                        $q[] = $f[1] . ':[* TO '.sprintf('%04d', $v[1]).']';
                    }
                } else if ($filter['type'] == 'text') {
                    $q[] = $filter['name'] . ':*' . str_replace(' ','*',$filter['value']) . '*';
                } else {
                    $q[] = $filter['name'].':"'.$filter['value'].'"';
                }
            }
        }

        $query = array(
            'q='.urlencode(implode(' AND ', $q)),
            'fl='.$fieldName,
            'facet=true',
            'facet.field=' . $fieldName,
            'facet.limit=' . __Config::get('metafad.dataGridSolr.autoComplete'),
            'wt=json',
            'rows=0'
        );

        if (__Config::get('DEBUG')) {
            $query[] = 'indent=true';
        }

        return $query;
    }
}
