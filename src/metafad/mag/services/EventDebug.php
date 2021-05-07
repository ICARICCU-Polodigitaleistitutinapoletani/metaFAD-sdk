<?php
class metafad_mag_services_EventDebug extends pinax_mvc_core_Proxy implements metafad_mag_services_EventInterface
{
    public function insert($decodeData)
    {
        var_dump($decodeData);
    }
}
