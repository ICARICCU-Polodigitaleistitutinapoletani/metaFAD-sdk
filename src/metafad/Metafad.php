<?php
class metafad_Metafad
{
    static $application;

    public static function init()
    {
        pinax_loadLocale('metafad');

        $log = pinax_log_LogFactory::create( 'DB', array(), __Config::get( 'metafad.log.level' ), __Config::get('metafad.log.group'));

        self::$application = pinax_ObjectValues::get('org.pinax', 'application');
    }


    public static function logOperation($msg, $group = '')
    {
        self::$application->dispatchEventByArray( PNX_LOG_EVENT, array('level' => PNX_LOG_SYSTEM,
            'group' => $group,
            'message' => $msg ));
    }

    public static function logAction($msg, $group = '')
    {
        self::$application->dispatchEventByArray( PNX_LOG_EVENT, array('level' => PNX_LOG_INFO,
            'group' => $group,
            'message' => $msg ));
    }

    public static function logError($msg, $group = '')
    {
        self::$application->dispatchEventByArray( PNX_LOG_EVENT, array('level' => PNX_LOG_ERROR,
            'group' => $group,
            'message' => $msg ));
    }
}