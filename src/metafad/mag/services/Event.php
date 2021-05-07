<?php
class metafad_mag_services_Event extends pinax_mvc_core_Proxy implements metafad_mag_services_EventInterface
{
    public function insert($decodeData)
    {
        $evt = array('type' => 'insertRecord', 'data' => array('data' => $decodeData, 'option' => array('commit' => true)));
        $this->dispatchEvent($evt);
    }
}
