<?php

namespace Statamic\API;

class TaxonomyTerm
{
    /**
     * @return \Statamic\Contracts\Data\Taxonomies\TermFactory
     */
    private static function factory()
    {
        return app('Statamic\Contracts\Data\Taxonomies\TermFactory');
    }

    /**
     * @param string $slug
     * @return \Statamic\Contracts\Data\Taxonomies\TermFactory
     */
    public static function create($slug)
    {
        return self::factory()->create($slug);
    }

    /**
     * Get a term from a taxonomy, by its slug
     *
     * @param string      $taxonomy
     * @param string      $slug
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public static function getFromTaxonomy($taxonomy, $slug, $locale = null, $fallback = false)
    {
        return Content::taxonomyTermRaw($slug, $taxonomy, $locale, $fallback);
    }

    /**
     * Get a taxonomy by UUID
     *
     * @param string $uuid
     * @param null   $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public static function getByUuid($uuid, $locale = null)
    {
        return Content::uuidRaw($uuid, $locale);
    }
}
