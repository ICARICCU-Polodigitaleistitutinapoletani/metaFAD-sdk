<?php
class metafad_common_views_components_ModalConfirmImport extends pinax_components_Component
{
    function init()
    {
        // define the custom attributes
        //$this->defineAttribute('label',     true,   '',         COMPONENT_TYPE_STRING);
        //$this->defineAttribute('message',    false,    '',     COMPONENT_TYPE_STRING);

        parent::init();
    }

    public function render($outputMode=NULL, $skipChilds=false)
    {
        $listaSchede = __Session::get("listaSchedePresenti");
        $schedaModel = __Session::get("schedaModel");
        $messaggio   = __Session::get("messaggio");


        $identificativolista = array(


            "aut200"        => 2,
            "aut300"        => 2,
            "bib200"        => 3,
            "bib300"        => 3,
            "schedabdm200"  => 0,
            "schedad200"    => 0,
            "schedad300"    => 1,
            "schedaf300"    => 1,
            "schedami200"   => 0,
            "schedami300"   => 0,
            "schedanu300"   => 1,
            "schedaoa200"   => 0,
            "schedaoa300"   => 1,
            "schedara300"   => 0,
            "schedas200"    => 0,
            "schedas300"    => 0,
            "schedaveac301" => 1,
            "aut300artin"   => 2,
            "bib300artin"   => 3,
            "schedad300artin"  => 4,
            "schedami300artin" => 4,
            "schedas300artin"  => 4

        );

        $campi = array(

            0 => array("nct_ss","ogtd_ss","sgti_ss","pvcc_ss","ldcn_ss"),
            1 => array("nct_ss","ogtd_ss","pvcc_ss","ldcn_ss"),
            2 => array("autn_ss","auth_ss"),
            3 => array("biba_ss","bibc_ss","bibg_ss","bibh_ss","bibt_ss"),
            4 => array("nct_ss","autn_ss","sgti_ss","invn_ss")
        );

        //$asd = pinax_locale_Locale.get();

        $campiSlc = $campi[$identificativolista[$schedaModel]];
       // $campiSlc = $listacampiImportanti[$schedaModel];
        $etichette = array();
        for($i=0;$i<count($campiSlc);$i++)
            $etichette[] = __T( strtoupper( str_replace("_ss","",$campiSlc[$i]) ) );

        $th="";
        for($i=0;$i<count($campiSlc);$i++)
            $th.='<th>'.$etichette[$i].'</th>';
        $th = '<tr>'.$th.'</tr>';
        $td = '';

        foreach( $listaSchede as $schede){

            if( count($schede)<=0) continue;


            foreach($schede as $scheda){
                $td.='<tr>';
                for($i=0;$i<count($campiSlc);$i++)
                    $td.='<td>'.$scheda->{$campiSlc[$i]}.'</td>';
                $td.='</tr>';
            }

        }

        $messaggio = "<h4>".$messaggio."</h4>";
        $table = '<table class="table table-striped table-bordered">'.$th.$td.'</table>';
        $pulsanti =  '<div class="modal-footer">
                        <button type="button" class="annulla btn btn-default js-pinaxcms-annulla" data-dismiss="modal" >Annulla</button>
                        <button type="button" class="btn btn-primary js-pinaxcms-import" data-dismiss="modal">Importa</button>
                      </div>';


        $output = $messaggio.$pulsanti.$table;



        $this->addOutputCode($output);
    }
}