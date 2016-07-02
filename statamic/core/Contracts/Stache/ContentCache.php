<?php

namespace Statamic\Contracts\Stache;

interface ContentCache
{
    /**
     * Get all timestamps
     *
     * @return array
     */
    public function getTimestamps();

    /**
     * Set all timestamps
     *
     * @param array $timestamps
     */
    public function setTimestamps($timestamps);

    /**
     * Set a single timestamp
     *
     * @param string $path
     * @param int $timestamp
     */
    public function setTimestamp($path, $timestamp);

    /**
     * Remove a single timestamp
     *
     * @param string $path
     */
    public function removeTimestamp($path);

    /**
     * Get the localized caches
     *
     * @return \Statamic\Contracts\Stache\LocalizedContentCache[]
     */
    public function getLocales();

    /**
     * Get a localized cache
     *
     * @param string|null $locale
     * @return \Statamic\Contracts\Stache\LocalizedContentCache
     */
    public function getLocale($locale = null);

    /**
     * Set a localized cache
     *
     * @param string                                          $locale
     * @param \Statamic\Contracts\Stache\LocalizedContentCache $cache
     */
    public function setLocale($locale, LocalizedContentCache $cache);
}
