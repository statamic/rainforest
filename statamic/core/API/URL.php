<?php

namespace Statamic\API;

/**
 * Manipulate URLs
 */
class URL
{
    /**
     * Removes occurrences of "//" in a $path (except when part of a protocol)
     * Alias of Path::tidy()
     *
     * @param string  $url  URL to remove "//" from
     * @return string
     */
    public static function tidy($url)
    {
        return Path::tidy($url);
    }

    /**
     * Assembles a URL from an ordered list of segments
     *
     * @param mixed string  Open ended number of arguments
     * @return string
     */
    public static function assemble($args)
    {
        $args = func_get_args();

        return Path::assemble($args);
    }

    /**
     * Takes a localized URL and returns the unlocalized, default version
     *
     * @param string      $url    The localized URL to transform
     * @param string|null $locale Optional locale to transform from
     * @return string
     */
    public static function unlocalize($url, $locale = null)
    {
        $urls = array_flip(content_cache()->getLocalizedUrls($locale));

        if ($uuid = array_get($urls, $url)) {
            return Page::getByUuid($uuid, Config::getDefaultLocale())->urlPath();
        }

        // todo: handle non-page urls

        return $url;
    }

    /**
     * Checks whether a URL exists
     *
     * @param string       $url     URL to find
     * @param string|null  $locale  Optional locale to use
     * @return bool
     */
    public static function exists($url, $locale = null)
    {
        return Content::urlExists($url, $locale);
    }

    /**
     * Get the slug of a URL
     *
     * @param string $url  URL to parse
     * @return string
     */
    public static function slug($url)
    {
        return basename($url);
    }

    /**
     * Swaps the slug of a $url with the $slug provided
     *
     * @param string  $url   URL to modify
     * @param string  $slug  New slug to use
     * @return string
     */
    public static function replaceSlug($url, $slug)
    {
        return Path::replaceSlug($url, $slug);
    }

    /**
     * Get the parent URL
     *
     * @param string $url
     * @return string
     */
    public static function parent($url)
    {
        $url_array = explode('/', $url);
        array_pop($url_array);

        $url = implode('/', $url_array);

        return ($url == '') ? '/' : $url;
    }

    /**
     * Make sure the site root is prepended to a URL
     *
     * @param  string $url
     * @param  boolean $controller   Whether to include the controller
     * @return string
     */
    public static function prependSiteRoot($url, $controller = true)
    {
        $prepend = SITE_ROOT;

        if ($controller && !REWRITE_URLS) {
            $prepend .= pathinfo(request()->getScriptName())['basename'];
        }

        return self::tidy(Str::ensureLeft($url, $prepend));
    }

    /**
     * Remove the site root from the start of a URL
     *
     * @param  string $url
     * @return string
     */
    public static function removeSiteRoot($url)
    {
        return self::tidy('/' . Str::removeLeft($url, SITE_ROOT));
    }

    /**
     * Make sure the site root url is prepended to a URL
     *
     * @param string      $url
     * @param string|null $locale
     * @return string
     */
    public static function prependSiteUrl($url, $locale = null)
    {
        return Str::ensureLeft(ltrim($url, '/'), Config::getSiteUrl($locale));
    }

    /**
     * Removes the site root url from the beginning of a URL
     *
     * @param string $url
     * @return string
     */
    public static function removeSiteUrl($url)
    {
        return preg_replace('#^'. Config::getSiteUrl() .'#', '/', $url);
    }

    /**
     * Make a full URL relative
     *
     * @param string $url
     * @return string
     */
    public static function makeRelative($url)
    {
        return parse_url($url)['path'];
    }

    /**
     * Get the current URL
     *
     * @return string
     */
    public static function getCurrent()
    {
        return format_url(app('request')->path());
    }

    /**
     * Formats a URL properly
     *
     * @param string $url
     * @return string
     */
    public static function format($url)
    {
        return self::tidy(format_url($url));
    }

    /**
     * Checks whether a URL is external or not
     * @param  string  $url
     * @return boolean
     */
    public static function isExternalUrl($url)
    {
        return ! Pattern::startsWith(URL::getSiteUrl());
    }

    /**
     * Get the current site url from Apache headers
     * @return string
     */
    public static function getSiteUrl()
    {
        $protocol = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'] . '/';

        return $protocol . $domainName;
    }

    /**
     * Build a page URL from a path
     *
     * @param string $path
     * @return string
     */
    public static function buildFromPath($path)
    {
        $path = Path::makeRelative($path);

        $ext = pathinfo($path)['extension'];

        $path = Path::clean($path);

        $path = preg_replace('/^pages/', '', $path);

        $path = preg_replace('#\/(?:[a-z]+\.)?index\.'.$ext.'$#', '', $path);

        return Str::ensureLeft($path, '/');
    }

    /**
     * Encode a URL
     *
     * @param string $url
     * @return string
     */
    public static function encode($url)
    {
        $dont_encode = [
            '%2F' => '/',
            '%40' => '@',
            '%3A' => ':',
            '%3B' => ';',
            '%2C' => ',',
            '%3D' => '=',
            '%2B' => '+',
            '%21' => '!',
            '%2A' => '*',
            '%7C' => '|',
            '%3F' => '?',
            '%26' => '&',
            '%23' => '#',
            '%25' => '%',
        ];

        return strtr(rawurlencode($url), $dont_encode);
    }
}
