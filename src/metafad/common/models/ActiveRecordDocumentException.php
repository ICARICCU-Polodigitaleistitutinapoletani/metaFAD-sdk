<?php
class metafad_common_models_ActiveRecordDocumentException extends Exception
{
    public static function instituteKeyEmpty()
    {
        return new self('La chiave istituto è vuota');
    }
}