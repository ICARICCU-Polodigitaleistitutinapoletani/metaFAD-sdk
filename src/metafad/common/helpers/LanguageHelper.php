<?php
class metafad_common_helpers_LanguageHelper extends PinaxObject
{
    public static function checkLanguage($model)
    {
        //Prima verifico che effettivamente sia abilitato il multilingua
        if(!__Config::get('metafad.hasMultiLanguage'))
        {
            return false;
        }

        //Controllo poi se il model in questione è multilingua o meno
        //(es: SBN non è multilingua)
        $m = pinax_ObjectFactory::createModel($model);
        if(method_exists($m,'canTranslate'))
        {
            return $m->canTranslate();
        }
        else
        {
            return false;
        }
    }

    public static function appendLanguagePrefix($id)
    {
        $application = pinax_ObjectValues::get('org.pinax', 'application');
        $languagePrefix = $application->getEditingLanguage();
        return $languagePrefix . '-' . $id;
    }
}