<?php
class metafad_gestioneDati_boards_views_helpers_BibHelper extends PinaxObject
{
  public function getBibValues($bib)
  {
    //Sistema il testo mostrato nelle schede BIB collegate in F, S, D, ecc...
    $newBib = array();
    $dataBib = $bib;
    foreach ($dataBib as $value) {
      $doc = pinax_ObjectFactory::createModelIterator('metafad.gestioneDati.boards.models.Documents')
            ->where('document_id',$value->__BIB->id)->first();
      if($doc->document_type == '')
      {
        continue;
      }
      $ar = pinax_ObjectFactory::createModel($doc->document_type.'.models.Model');

      $ar->load($value->__BIB->id);

      $findTermFields = $ar->getFindTermFields();
      $text = array();
      foreach ($findTermFields as $field) {
        if(is_array($field))
        {
          $field1 = $field[1];
          $text[] = ($ar->$field1) ? $field[0] . $ar->$field1 : $ar->document_id;
        }
        else
        {
          $text[] = pinax_strtrim($ar->$field, 50);
        }
      }

      $value->__BIB->text = implode(' - ', array_filter($text));
      $newBib[] = $value;
    }
    return $newBib;
  }
}
