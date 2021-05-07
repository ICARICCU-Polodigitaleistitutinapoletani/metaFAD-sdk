<?php
class metafad_common_views_components_AddButton extends pinax_components_Component
{
    function init()
    {
        // define the custom attributes
		$this->defineAttribute('type', false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('label', false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('routeUrl', false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('recordClassName', false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('createFromTemplate', false, false, COMPONENT_TYPE_STRING);

        parent::init();
    }

    public function render($outputMode=NULL, $skipChilds=false)
    {
        $types = ($this->getAttribute('type')) ? explode(',', $this->getAttribute('type')) : '';
        if($types){
            $labels = explode(',', $this->getAttribute('label'));
            $routeUrl = $this->getAttribute('routeUrl');
            $output = '<div id="dataGridAddButton"><div class="btn-group btn-right-accessory">'.
                    '<a class="btn btn-info btn-flat btn-add dropdown-toggle" data-toggle="dropdown" href="#">'.
                    '<i class="fa fa-caret-down"></i> '.
                    '</a>'.
                    '<ul class="dropdown-menu forced-left-position">';

            if($types && !empty($types)){
                foreach ($types as $i => $type) {
                    $output .= '<li>'.__Link::makeLink($routeUrl, array('sectionType' => $type, 'id' => 0, 'label' => 'Crea scheda: '.$labels[$i])).'</li>';
                }
            }

            if($this->getAttribute('createFromTemplate')){
                $it = pinax_ObjectFactory::createModelIterator($this->getAttribute('recordClassName'));
                foreach ($it as $ar) {
                    if ($ar->fieldExists('isTemplate') && $ar->isTemplate) {
                        $output .= '<li>'.__Link::makeLink(
                            $routeUrl. 'Template',
                            array(
                                'type' => $type,
                                'templateID' => $ar->getId(),
                                'id' => 0,
                                'label' => 'Crea '. $type. ' da: '. $ar->templateTitle
                            )
                        ).'</li>';
                    }
                }
            }

            $output .= '</ul>'.
                    '</div>'.
                    '<a id="primaryButton" class="btn btn-info btn-flat btn-add" href="#">Aggiungi scheda</a></div>';

            $dataGridId = $this->getAttribute('dataGridAjaxId');
            $output .= <<<EOD
    <script type="text/javascript">
        jQuery(function(){
            jQuery('#primaryButton').click( function () {
                jQuery('.btn-right-accessory .dropdown-menu').toggle();
            });
            jQuery('.btn.btn-info.btn-flat.btn-add.dropdown-toggle').click( function () {
                jQuery('.btn-right-accessory .dropdown-menu').toggle();
            });
            var table = jQuery('#$dataGridId').data('dataTable');
            setTimeout(function(){
                jQuery('#dataGridAddButton').prependTo("#{$dataGridId}_wrapper .filter-row");

            }, 100);
        });
    </script>
    EOD;
            $this->addOutputCode($output);
        }
        else{
            $routeUrl = $this->getAttribute('routeUrl');
            $output = '<div id="dataGridAddButton" class="' . $this->getAttribute('cssClass') . '"><div class="btn-group btn-right-accessory">'.
                    '</div>'.__Link::makeLink($routeUrl, array('label' => '<span>'.$this->getAttribute('label').'</span>', 'cssClass' => 'btn btn-info btn-flat btn-add', 'icon' => 'plusIcon fa fa-plus','title' => $this->getAttribute('add')),array(),'', false).'</div>';
                $dataGridId = $this->getAttribute('dataGridAjaxId');
                $output .= <<<EOD
        <script type="text/javascript">
            jQuery(function(){
                var table = jQuery('#$dataGridId').data('dataTable');
                setTimeout(function(){
                    jQuery('#dataGridAddButton').prependTo("#{$dataGridId}_wrapper .filter-row");

                }, 100);
            });
        </script>
        EOD;
        $this->addOutputCode($output);
        }
    }
}