<?php

namespace Statamic\Contracts\Stache;

interface AssetCache
{
    /**
     * Get the localized caches
     *
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache[]
     */
    public function getLocales();

    /**
     * Get a localized cache
     *
     * @param string|null $locale
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache
     */
    public function getLocale($locale = null);

    /**
     * Set a localized cache
     *
     * @param string                                          $locale
     * @param \Statamic\Contracts\Stache\LocalizedAssetCache $cache
     */
    public function setLocale($locale, LocalizedAssetCache $cache);
}
