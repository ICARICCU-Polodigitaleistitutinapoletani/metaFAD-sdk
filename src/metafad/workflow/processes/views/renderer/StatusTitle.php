<?php
class metafad_workflow_processes_views_renderer_StatusTitle extends pinax_components_render_RenderCell
{
    public function renderCell($key, $value, $row, $columnName)
    {
        if(!$value){
            return 'Non avviato';
        }
        else if($value == '1'){
            return 'In corso';
        }
        else{
            return 'Completato';
        }
    }
}