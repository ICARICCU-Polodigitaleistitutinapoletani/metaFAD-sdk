<?php
class metafad_usersAndPermissions_IstituteCheckListener extends PinaxObject
{
    function __construct()
    {
        $this->addEventListener(PNX_EVT_BEFORE_CREATE_PAGE, $this);
        $this->addEventListener(PNX_EVT_USERLOGIN, $this);
    }

    public function login($event = null)
    {
        $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
        if(!$instituteKey){
            pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('link', array('pageId' => __Config::get('metafad.accessPage'))));
        }
    }

    public function beforeCreatePage($event = null)
    {
        $user = $event->target->_user;
        $pageId = __Request::get('pageId');

        $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();

        // sulle pagine pubbliche il redirect non va fatto
        if ($user->id && !$instituteKey && !in_array($pageId, array('', 'utenti-e-permessi-selezione-istituto', 'utenti-e-permessi-istituto-mancante', 'Login', 'Logout'))) {
            // se l’utente appartiene a più istituti l’utente sceglierà con quale istituto entrare
            $relationsProxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.relations.models.proxy.RelationsProxy');

            if ($relationsProxy->hasMoreInstitutes($user->id)){
                pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('link', array('pageId' => 'utenti-e-permessi-selezione-istituto')));
            } else {
                $instituteId = $relationsProxy->getInstituteId($user->id);

                if ($instituteId) {
                    $instituteProxy = pinax_ObjectFactory::createObject('metafad.usersAndPermissions.institutes.models.proxy.InstitutesProxy');
                    $institute = $instituteProxy->getInstituteVoById($instituteId);
                    metafad_usersAndPermissions_Common::setInstituteKey($institute->institute_key);
                    $evt = array('type' => 'reloadAcl');
                    $this->dispatchEvent($evt);
                    $this->checkBackEndAccess($user);
                    pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('link', array('pageId' => $pageId)));
                } else {
                    pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('link', array('pageId' => 'utenti-e-permessi-istituto-mancante')));
                }
            }
        }

        if ($user->id && $instituteKey && in_array($pageId, array('dashboard', 'home'))) {
            $this->checkBackEndAccess($user);
        }

        __Config::set('metafad.dam.instance', $instituteKey);

        if ($pageId == 'Logout') {
            metafad_usersAndPermissions_Common::setInstituteKey(null);
        }
    }

    protected function checkBackEndAccess($user)
    {
        if (!$user->acl('home', 'all')) {
    		__Session::set('pinax.user', null);
    		__Session::set('pinax.userLogged', false);
            __Session::set('pinax.loginError', pinax_locale_Locale::get('LOGGER_INSUFFICIENT_GROUP_LEVEL'));
            pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('link', array('pageId' => 'Login')));
        }
    }
}