<?php

namespace Statamic\API;

class TaxonomyTerms
{
    /**
     * Get taxonomies from a group
     *
     * @param string      $taxonomy
     * @param array|null  $slugs
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\ContentCollection
     */
    public static function getFromTaxonomy($taxonomy, $slugs = null, $locale = null, $fallback = false)
    {
        $terms = Content::taxonomyTerms($taxonomy, $locale, $fallback);

        if ($slugs) {
            $slugs = Helper::ensureArray($slugs);

            $terms = $terms->filter(function ($taxonomy) use ($slugs) {
                return in_array($taxonomy->slug(), $slugs);
            });
        }

        return $terms;
    }
}
