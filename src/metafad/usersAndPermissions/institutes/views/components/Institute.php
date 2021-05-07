<?php
class metafad_usersAndPermissions_institutes_views_components_Institute extends pinax_components_Component
{
    public function render($outputMode=NULL, $skipChilds=false)
    {
        $instituteName = metafad_usersAndPermissions_Common::getInstituteName();

        $output = <<<EOD
<label>Istituto: $instituteName</label>
EOD;
        $this->addOutputCode($output);
    }
}