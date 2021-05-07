<?php
class metafad_print_views_components_Preview extends pinax_components_Component
{
	function init()
	{
        // define the custom attributes
		$this->defineAttribute('model', false, '', COMPONENT_TYPE_STRING);

		parent::init();
	}

	function process()
	{
		if (__Request::get('action')) {
			$id = explode('-',rtrim(__Request::get('action'),'-'));
		}
		$model = (__Request::get('model')) ? : $this->getAttribute('model');

		if(!$model && $id)
		{
			$request = pinax_ObjectFactory::createObject(
				'pinax.rest.core.RestRequest',
				__Config::get('metafad.solr.url') . 'select?wt=json&q=id:"'.current($id).'"'
			);
			$request->setTimeout(1000);
			$request->setAcceptType('application/json');
			$request->execute();
			$model = current(json_decode($request->getResponseBody())->response->docs[0]->document_type_t);
		}

		$ids = (__Request::get('id')) ? array(__Request::get('id')) : $id;
		foreach ($ids as $id) {
			$record = pinax_ObjectFactory::createModel($model);
			$record->load($id);
			if ($record) {
				$moduleService = pinax_ObjectFactory::createObject('metafad.common.services.ModuleService');
				$elements = $moduleService->getElements(str_replace('_preview', '', str_replace('.models.Model', '', $model)));

				$record = $record->getRawData();
				$record->__model = $model;
				$record->__id = $record->document_id;
				$helper = pinax_ObjectFactory::createObject('metafad_solr_helpers_PreviewHelper');
				$detail = $helper->detailMapping($record, $elements);
				$output = '';
				foreach ($detail as $k => $v) {
					if (strpos($k, 'html_') !== false) {
						$key = str_replace('_html_nxtxt', '', $k);
						$key = str_replace('_html_nxt', '', $key);
						$keySplit = explode('_', $key);
						$key = __T(strtoupper(end($keySplit)));

						$output .= '<div style="page-break-inside: avoid;"><span class="label-preview">' . $key . '</span><br/>' . str_replace('label', 'label-preview-internal', $v) . '</div><br/>';
					}
				}
				$output .= '<div style="page-break-before: always;"></div>';
			}

			$this->addOutputCode($output);
		}

	}
}