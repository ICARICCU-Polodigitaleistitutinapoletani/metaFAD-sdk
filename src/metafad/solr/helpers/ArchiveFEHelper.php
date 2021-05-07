<?php
class metafad_solr_helpers_ArchiveFEHelper extends PinaxObject
{
	//Indicare nell'array $singleField i campi, definiti in mappingArchive.json, da salvare anche con il suffisso _s per l'ordinamento
	private $singleField = ["denominazione_titolo", "denominazione", "cronologia_datazione_ECT"];
	private $fieldsLabel;
	private $mappingHelper;

	public function mappingFE($data, $option = 'commit')
	{
		$indexOnMeta = false;
		$model = $data->__model;
		$this->fieldsLabel = new stdClass();
		$thrower = new PinaxObject();
		$docForSolr = new stdClass();
		$hasImageHelper = pinax_ObjectFactory::createObject('metafad.solr.helpers.HasImageHelper');
		$this->mappingHelper = pinax_ObjectFactory::createObject('metafad.solr.helpers.MappingHelper');
		$jsonPath = __Config::get('pathToAdmin') . 'classes/userModules/archivi/json/';
		$mappingArchive = json_decode(file_get_contents(__Config::get('pathToAdmin') . 'classes/metafad/solr/json/mappingArchive.json'));
		$solrUrl = __Config::get('metafad.solr.archive.url');
		$sortingFields = json_decode(__Config::get('metafad.archive.solr.sortingFields'));
		//check della visibilità
		$visibility = $this->checkVisibility($data);
		if ($visibility !== '0') {
			$docForSolr->visibility_nxs = $visibility;
		} else {
			return;
		}


		//CAMPI COMUNI
		$docForSolr->id = $data->__id;
		$docForSolr->institutekey_s = $data->instituteKey;
		$docForSolr->model_nxs = $model;

		if ($model == 'archivi.models.ComplessoArchivistico') {
			$elements = json_decode(file_get_contents($jsonPath . 'ComplessoArchivistico.json'));
			$values = $this->iterateElements($elements, $data);
			$docForSolr->type_archive_s = 'ca';
			$docForSolr->sorting_i = $data->__id;
		} else if ($model == 'archivi.models.UnitaDocumentaria' || $model == 'archivi.models.UnitaArchivistica') {
			$jsonFile = ($model == 'archivi.models.UnitaDocumentaria') ? 'UnitaDocumentaria.json' : 'UnitaArchivistica.json';
			$elements = json_decode(file_get_contents($jsonPath . $jsonFile));
			$docForSolr->type_archive_s = 'u';
			$docForSolr->sorting_i = $data->__id;
		} else if ($model == 'archivi.models.ProduttoreConservatore' || $model == 'archivi.models.ProgettoDiDigitalizzazione') {
			$jsonFile = ($model == 'archivi.models.ProduttoreConservatore') ? 'ProduttoreConservatore.json' : 'ProgettoDigitalizzazione.json';
			$elements = json_decode(file_get_contents($jsonPath . $jsonFile));
			$solrUrl = __Config::get('metafad.solr.archiveaut.url');
		} else {
			return;
		}

		if ($model == 'archivi.models.ProgettoDiDigitalizzazione') {
			$progettoRange = $this->calculateEstremiProgetto($data);
			if ($progettoRange)
				$docForSolr->intervalloCronologico_s =  $progettoRange;
		}

		$docForSolr->data_s = $this->iterateElements($elements, $data);

		$searchFields = $this->getMappingForSearch($mappingArchive->$model, $data);
		foreach ($searchFields as $key => $value) {
			if (in_array($key, $sortingFields)) {
				if (!$docForSolr->{$key . '_sort_s'}) {
					$docForSolr->{$key . '_sort_s'} = (is_string($value)) ? strtolower($value) : strtolower($value[0]);
				}
			}
			if ($key === 'parent' || $key === 'ordinamentoProvvisorio') {
				$docForSolr->{$key . '_i'} = $value;
				continue;
			}
			if (strpos($key, 'year:') === 0) {
				$key = str_replace('year:', '', $key);
				if ($key == 'estremoRemoto') {
					$value = min($value);
				} else {
					$value = max($value);
				}
				$docForSolr->{$key . '_i'} = $value;
				continue;
			}
			$docForSolr->{$key . '_ss'} = $value;
			if (in_array($key, $this->singleField)) {
				$stringVal = '';
				foreach ($value as $val) {
					if (is_array($val)) {
						continue;
					}
					$stringVal .= "$val, ";
				}
				$singleValue = strtolower(trim($stringVal, ', '));
				$docForSolr->{$key . '_s'} = $singleValue;
				if ($key != 'cronologia_datazione_ECT') {
					$docForSolr->titolo_s = $singleValue;
				}
			}
			foreach ($value as $v) {
				//TODO da gestire il caso dell'array nell'array (esempio: cronologia in denominazione scheda Entità)
				if ($key === 'complessiLinked' || is_array($v)) {
					continue;
				}
				$docForSolr->{$key . '_txt'}[] = strtolower($v);
			}
		}

		//IMMAGINE, solo UD, UA e Progetto di Digitalizzazione ce l'hanno
		if ($model == 'archivi.models.UnitaDocumentaria' || $model == 'archivi.models.UnitaArchivistica' || $model = 'archivi.models.ProgettoDiDigitalizzazione') {
			//Prendo le info per la prima immagine da avere in SOLR
			$fi = pinax_ObjectFactory::createObject('metafad.viewer.helpers.FirstImage');
			$firstImage = $fi->execute($docForSolr->id, 'archive');
			if ($firstImage) {
				$docForSolr->digitale_idpreview_s = $firstImage['firstImage'];
				$docForSolr->digitale_idpreview_t = $firstImage['firstImage'];
			}
		}

		//Prendo il primo livello (utile per linkarlo nella scheda del dettaglio)
		if ($data->root != 'true') {
			$firstLevel = $this->getFirstLevel($data);
			$docForSolr->primo_livello_id_s = $firstLevel['id'];
			$docForSolr->primo_livello_label_s = $firstLevel['text'];
			$complex_s = explode('|', $firstLevel['text'])[0];
			$docForSolr->complex_s = explode('|', $complex_s);
			//Salva la denonimazione del livello immediatamente superiore
			$parentTitle = trim(explode('||', $data->parent->text)[1]);
			if (!$parentTitle) {
				$parentTitle = $this->getParentDen($data->parent->id);
			}
			$docForSolr->parent_s = $parentTitle;
			$conservatore = $this->getConservatore($firstLevel['id']);
			$produttori = $this->getProduttore($firstLevel['id']);
			if ($conservatore) {
				//Salva la denominazione del conservatore nelle unità
				if ($data->__model != 'archivi.models.ComplessoArchivistico') {
					$docForSolr->conservatoreDen_ss = trim(explode('||', $conservatore->text)[1]);
					$docForSolr->conservatore_tipo_ss = $this->detectProdConsType($conservatore->id);
					$docForSolr->conservatoreDen_txt = $docForSolr->conservatoreDen_ss;
					$docForSolr->conservatore_tipo_txt = $docForSolr->conservatore_tipo_ss;
					if ($produttori) {
						foreach ($produttori as $p) {
							$docForSolr->produttoreDen_ss[] = trim(explode('||', $p->soggettoProduttore->text)[1]);
							$docForSolr->produttore_tipo_ss[] = $this->detectProdConsType($p->soggettoProduttore->id);
						}
					}
					if ($produttori) {
						$docForSolr->produttore_tipo_ss = array_unique($docForSolr->produttore_tipo_ss);
						$docForSolr->produttoreDen_txt = $docForSolr->produttoreDen_ss;
						$docForSolr->produttore_tipo_txt = $docForSolr->produttore_tipo_ss;
					}
				} else {
					$docForSolr->conservatore_tipo_ss = $this->detectProdConsType($conservatore->id);
					$docForSolr->conservatore_tipo_txt = $docForSolr->conservatore_tipo_ss;
					if ($produttori) {
						foreach ($produttori as $p) {
							$docForSolr->produttore_tipo_ss[] = $this->detectProdConsType($p->soggettoProduttore->id);
						}
					}
					if ($produttori) {
						$docForSolr->produttore_tipo_ss = array_unique($docForSolr->produttore_tipo_ss);
						$docForSolr->produttore_tipo_txt = $docForSolr->produttore_tipo_ss;
					}
				}
				$acronymString = $this->generateAcronymTitle($conservatore, $complex_s);
				if ($acronymString) {
					$docForSolr->denominazioneAcronimo_ss = $acronymString;
				}
			}
		} else {
			if ($data->__model == 'archivi.models.ComplessoArchivistico') {
				$docForSolr->ordinamentoProvvisorio_i = 0;
				$conservatore = $this->getConservatore($data->__id);
				$produttori = $this->getProduttore($data->__id);
				$docForSolr->conservatore_tipo_ss = $this->detectProdConsType($conservatore->id);
				$docForSolr->conservatore_tipo_txt = $docForSolr->conservatore_tipo_ss;
				if ($produttori) {
					foreach ($produttori as $p) {
						$docForSolr->produttore_tipo_ss[] = $this->detectProdConsType($p->soggettoProduttore->id);
					}
				}
				if ($produttori) {
					$docForSolr->produttore_tipo_ss = array_unique($docForSolr->produttore_tipo_ss);
					$docForSolr->produttore_tipo_txt = $docForSolr->produttore_tipo_ss;
				}
			}
		}

		//Metadati strutturali per viewer
		$this->checkStruData($data, $docForSolr);

		$evt = array('type' => 'insertData', 'data' => array('data' => $docForSolr, 'option' => array(($option === "queue" ? "queue" : "commit") => true, 'url' => $solrUrl)));
		$thrower->dispatchEvent($evt);

		//METAINDICE
		$metaindiceHelper = pinax_ObjectFactory::createObject('metafad.solr.helpers.MetaindiceHelper');
		$metaindiceHelper->mapping($data, 'archive', 'commit', $docForSolr);
	}

	protected function getMappingForSearch($mapping, $data)
	{
		$doc = new stdClass();
		foreach ($mapping as $key => $fields) {
			$doc->$key = array();
			$doc->conservatoriDen = array();

			foreach ($fields as $field) {
				//Campo classico
				if (strpos($field, 'link:') !== 0 && !strpos($field, '.')) {
					if ($data->$field) {
						if (is_string($data->$field) || is_numeric($data->$field)) {
							array_push($doc->$key, $data->$field);
						} else if (is_array($data->$field)) {
							foreach ($data->$field as $f) {
								if (is_object($f)) {
									foreach ($f as $objVal) {
										if (is_object($objVal)) {
											array_push($doc->$key, $objVal->text);
										} else if (is_array($objVal)) {
											continue;
										} else if ($objVal) {
											array_push($doc->$key, $objVal);
										}
									}
								} elseif (is_string($f)) {
									array_push($doc->$key, $f);
								}
							}
						}
					}
				}
				//Campo con link, va gestito in FE dal dettaglio
				else if (strpos($field, 'link:') === 0) {
					$fieldKeys = explode('link:', $field);
					$fieldName = $fieldKeys[1];
					if ($data->$fieldName) {
						if ($fieldName === 'soggettoConservatore') {
							$consArr = explode('||', $data->$fieldName->text);
							array_push($doc->$key, trim($consArr[0]));
							$denKey = $key . 'Den';
							$doc->$denKey = array();
							$consDen = trim($consArr[1]);
							array_push($doc->$denKey, $consDen);
						} else {
							array_push($doc->$key, $data->$fieldName->text);
							if ($fieldName === 'parent') {
								$doc->parent = array();
								array_push($doc->parent, $data->$fieldName->id);
							}
						}
					}
				}
				//Campo da cui pescare sottocampi sottocampi
				else if (strpos($field, '.')) {
					$isCronologia = false;
					if (strpos($field, 'year:') === 0) {
						$isCronologia = true;
						$field = str_replace('year:', '', $field);
					}
					$fieldKeys = explode('.', $field);
					$parentField = $fieldKeys[0];
					$childField = $fieldKeys[1];
					if ($data->$parentField) {
						foreach ($data->$parentField as $values) {
							if ($values->$childField) {
								if (is_object($values->$childField)) {
									if ($childField == "soggettoConservatore") {
										$consArr = explode('||', $values->$childField->text);
										if (!$consArr[0]) {
											continue;
										}
										array_push($doc->$key, trim($consArr[0]));
										array_push($doc->conservatoriDen, trim($consArr[1]));
									} else {
										array_push($doc->$key, $values->$childField->text);
									}
								} else {
									array_push($doc->$key, $values->$childField);
									if ($isCronologia) {
										$this->separateYearForSearch($doc, $values, $childField);
									}
								}
							}
						}
					}
				}
			}
			if (empty($doc->$key)) {
				unset($doc->$key);
			}
		}

		return $doc;
	}

	protected function separateYearForSearch($doc, $cronologia, $childField)
	{

		$year = explode('/', $cronologia->$childField)[0];
		if (strpos(strtolower($childField), 'remoto') !== false) {
			$key = 'year:estremoRemoto';
			$codificaField = "estremoRemoto_codificaData";
		} else if (strpos(strtolower($childField), 'recente') !== false) {
			$key = 'year:estremoRecente';
			$codificaField = "estremoRecente_codificaData";
		} else {
			return;
		}
		if (!isset($doc->$key)) {
			$doc->$key = array();
		}
		if (!is_numeric($year)) {
			$codifica = $cronologia->$codificaField;
			$year = strlen($codifica) < 17 ? substr($codifica, 0, 3) : substr($codifica, 0, 4);
		}
		array_push($doc->$key, (int)$year);
	}

	protected function iterateElements($elements, $data)
	{
		$values = array();
		//Campi di sistema da non includere mai nel dettaglio
		$toSkip = array('tabMediaCollegati');
		foreach ($elements->tabs as $tab) {
			if (in_array($tab->id, $toSkip)) {
				continue;
			}
			$valuesTab = $this->processTabs($tab, $data);
			if (!empty($valuesTab)) {
				array_push($values, $valuesTab);
			}
		}

		$finalValues = array();
		foreach ($values as $v) {
			foreach ($v as $fv) {
				$finalValues[] = $fv;
			}
		}
		return json_encode($finalValues);
	}

	protected function processTabs($tab, $content)
	{
		$values = $this->processElements($tab->fields, $content);
		return $values;
	}

	protected function processElements($elements, $content)
	{
		$values = array();
		foreach ($elements as $element) {
			$obj = new StdClass();
			if ($element->children) {
				$value = $content->{$element->id};
				if (is_array($value) && empty($value)) {
					continue;
				}
				$obj->type = 'group';
				$obj->name = $element->id;
				$obj->label = ($element->label) ?: $element->attributes->label;
				$obj->children = array();

				if ($element->required === 'true') {
					$childValue = $this->processElements($element->children, $content);
					if (!empty($childValue)) {
						$obj->children[] = $childValue;
					}
				} else if ($element->type == 'Fieldset') {
					$childValue = $this->processElements($element->children, $content);
					if (!empty($childValue)) {
						$obj->children[] = $childValue;
					}
				} else if ($value) {
					foreach ($value as $childContent) {
						$childValue = $this->processElements($element->children, $childContent);
						if (!empty($childValue)) {
							$obj->children[] = $childValue;
						}
					}
				}


				if (!empty($obj->children)) {
					$values[] = $obj;
				}
			} else {
				$value = $content->{$element->id};
				if (is_object($value) && !$value->id) {
					continue;
				}
				if (!empty($value) || $value === '0' || $value === 0) {
					$obj->type = 'field';
					$obj->name = $element->id;
					$obj->label = $element->label;
					if (is_object($value)) {
						$this->splitText($value);
					}
					if ($element->id == "estremoCronologicoTestuale") {
						$value = $this->revertData($value);
					} elseif ($element->id == "contestoProvenienza_descrizione") {
						$value = $this->cleanRegestoTag($value);
					} elseif ($element->id == 'autoreCognomeNome') {
						$value = $this->cleanAntroponimi($value);
					}
					$obj->value = (is_object($value)) ? array($value) : $value;
					$values[] = $obj;
				}
			}
		}
		return $values;
	}

	public function checkVisibility($data)
	{
		if ($data->visibility === '0') {
			//Cancellazione da FE
			$archProxy = __ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
			$archProxy->delete($data->__id, true, false, true);
		}
		return $data->visibility;
	}

	public function checkStruData($data, $docForSolr)
	{
		if ($data->uuidImagePrimary) {
			$docForSolr->startImage1_nxs = $data->uuidImagePrimary->id;
		}
		if ($data->secondaryStruMag) {
			$docForSolr->secondaryStru_nxs = true;
			$docForSolr->startImage2_nxs = $data->secondaryStruMag[0]->uuidImageSecondary->id;
		}
	}

	public function getFirstLevel($data)
	{
		$record = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
		$record->load($data->parent->id, 'PUBLISHED_DRAFT');
		$archProxy = __ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
		if ($record->root == 'true') {
			$title = $record->_denominazione;
			$title = explode("||", $title);

			$nome = trim($title[1], " ");
			$data = trim($title[2], " ");
			$data = $data == "-" ? "" : $data;

			$finalTitle = ($data) ? $nome . '|' . $data : $nome;
			return array('id' => $record->document_id, 'text' => $finalTitle);
		} else {
			return $this->getFirstLevel($record);
		}
	}

	public function getConservatore($id)
	{
		$record = pinax_ObjectFactory::createModel('archivi.models.ComplessoArchivistico');
		$record->load($id);
		$rawData = $record->getRawData();
		if (property_exists($rawData, 'soggettoConservatore')) {
			return $rawData->soggettoConservatore;
		}
		return null;
	}

	public function getProduttore($id)
	{
		$record = pinax_ObjectFactory::createModel('archivi.models.ComplessoArchivistico');
		$record->load($id);
		$rawData = $record->getRawData();
		return $rawData->produttori;
	}

	public function generateAcronymTitle($conservatore, $den)
	{
		$record = pinax_ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
		$record->load($conservatore->id);
		$altriCodici = $record->altriCodiciIdentificativi;
		if (!count($altriCodici)) {
			return null;
		}
		$cod = trim($altriCodici[0]->codice);
		$cod = str_replace('IT-', '', $cod);
		return "$den ($cod)";
	}

	public function getParentDen($id)
	{
		$record = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
		$record->load($id, 'PUBLISHED_DRAFT');
		$string = $record->_denominazione;
		$title = explode("||", $string);
		return trim($title[1]);
	}

	//Salva solo la denominazione di un oggetto (per evitare di mostrare codici in FE)
	public function splitText($value)
	{
		$textArr = explode('||', $value->text);
		if (count($textArr) > 1) {
			$value->text = trim($textArr[1]);
		}
	}

	public function revertData($value)
	{
		$dataArr = explode('-', $value);
		if (count($dataArr) > 1) {
			$data1 = trim($dataArr[0]);
			$data2 = trim($dataArr[1]);
			if ($data1 == $data2) {
				array_pop($dataArr);
			}
		}
		$res = '';
		foreach ($dataArr as $data) {
			$arr = explode('/', trim($data));
			$arr = array_reverse($arr);
			$res .= ' - ' . implode('/', $arr);
		}
		return trim($res, ' - ');
	}

	public function cleanRegestoTag($value)
	{
		$value = str_replace('REG (REGESTO):', '', $value);
		return trim($value);
	}

	public function cleanAntroponimi($value)
	{
		//$value->text = str_replace(['&lt;', '&gt;'], ['<', '>'], $value->text);
		$length = strpos($value->text, '&lt');
		if ($length === false)
			return $value;
		$value->text = trim(substr($value->text, 0, $length));
		return $value;
	}

	public function calculateEstremiProgetto($data)
	{
		$complessi = $data->complessi;
		$ids = '';
		foreach ($complessi as $c) {
			$ids .= 'id%3A' . $c->linkComplessi->id . '+';
		}
		if (!$ids) {
			return '';
		}
		$ids = trim($ids, '+');
		$url = __Config::get('metafad.solr.url') . "select?q=$ids&fl=estremoRemoto_i+estremoRecente_i&wt=json&indent=true";
		$method = 'GET';
		$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url, $method, null, 'application/json');
		$request->setTimeout(1000);
		$request->setAcceptType('application/json');
		$request->execute();

		$responseInfo = $request->getResponseInfo();
		$status = $responseInfo['http_code'];
		if ($status != 200) {
			return '';
		}
		$responseBody = str_replace('\\/', '/',  $request->getResponseBody());

		$range = '';
		$date = [];
		$resultDecoded = json_decode($responseBody);
		if ($docs = $resultDecoded->response->docs) {
			foreach ($docs as $d) {
				$val1 = $d->estremoRemoto_i;
				if ($val1) {
					$date[] = $val1;
				}
				$val2 = $d->estremoRecente_i;
				if ($val2) {
					$date[] = $val2;
				}
			}
		}
		if (!$date) {
			return '';
		}
		$min = min($date);
		$max = max($date);
		$range = "$min - $max";
		return $range;
	}

	function detectProdConsType($id)
	{
		$ar = __ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
		if ($ar->load($id)) {
			return $ar->tipologiaChoice;
		}
	}
}
