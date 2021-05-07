<?php
class iiif_services_Common
{
    static function makeDetailLink($ar)
    {
        return PNX_HOST . '/' . $ar->document_uid;
    }
    static function makeIIIFLink($ar)
    {
        return __Link::makeUrl('iiifimage', array(
            'uid' => urlencode(urlencode($ar->document_uid)),
            'region' => 'full',
            'size' => 'full',
            'rotation' => 0,
            'quality' => 'default',
            'format' => 'jpg'
        ));
    }
    static function makeIIIFLinkImageMetadata($ar)
    {
        return __Link::makeUrl('iiifimage_metadata', array(
            'uid' => urlencode(urlencode($ar->document_uid)),
        ));
    }
    static function makeIIIFManifestLink($ar)
    {
        return __Link::makeUrl('manifest', array(
            'uid' => urlencode(urlencode($ar->document_uid)),
        ));
    }
    static function getImageWithSize($uuid, $size = 'full', $internal = true, $region = 'full')
    {
        if($internal)
        {
            return __Config::get('iiif.imagePath.internal') . 'iiif/2/' . $uuid . '/'.$region.'/'.$size.'/0/default.jpg';
        }
        else
        {
            return __Config::get('iiif.imagePath') . 'iiif/2/' . $uuid . '/' . $region . '/' . $size . '/0/default.jpg';
        }
    }
    static function getRecordMd5($ar)
    {
        return md5($ar->document_uid);
    }
    static function getZoom($ar)
    {
        return "openZoom('" . $ar->document_uid . "', '" . self::getRecordMd5($ar) . "' )";
    }
    static function formatMultilineValues($values, $comma = true)
    {
        return nl2br($comma ? str_replace(', ', '<br />', $values) : $values);
    }
    static function formatMultilineValuesForNote($values)
    {
        return nl2br(str_replace('. - ', '<br />', $values));
    }
    static function formatTooltip($ar, $v1, $v2 = null)
    {
        $v1 = explode("\n", $ar->$v1);
        return pinax_strtrim($v1[0], 100) . ($v2 ? ' - ' . $ar->$v2 : '');
    }
    static function formatDate($values)
    {
        return strlen($values) > 12 ? substr($values, 12) : $values;
    }
    
    static function composeMultipleValues($values)
    {
        return implode(' - ', array_filter($values, 'strlen'));
    }
}
