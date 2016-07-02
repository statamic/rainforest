<?php

namespace Statamic\API;

use Stringy\StaticStringy as Stringy;

/**
 * Manipulating strings
 */
class Str extends \Illuminate\Support\Str
{
    public static function ensureLeft($string, $char)
    {
        return Stringy::ensureLeft($string, $char);
    }

    public static function ensureRight($string, $char)
    {
        return Stringy::ensureRight($string, $char);
    }

    public static function removeLeft($string, $char)
    {
        return Stringy::removeLeft($string, $char);
    }

    public static function removeRight($string, $char)
    {
        return Stringy::removeRight($string, $char);
    }

    public static function replace($string, $search, $replacement)
    {
        return Stringy::replace($string, $search, $replacement);
    }

    public static function studlyToSlug($string)
    {
        return Str::slug(Str::snake($string));
    }

    public static function isUrl($string)
    {
        return self::startsWith($string, ['http://', 'https://', '/']);
    }
}
