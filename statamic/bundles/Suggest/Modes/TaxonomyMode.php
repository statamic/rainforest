<?php

namespace Statamic\Addons\Suggest\Modes;

use Statamic\API\TaxonomyTerms;

class TaxonomyMode extends AbstractMode
{
    public function suggestions()
    {
        $suggestions = [];

        $taxonomy = $this->request->input('taxonomy');

        $terms = TaxonomyTerms::getFromTaxonomy($taxonomy);

        $terms = $terms->multisort($this->request->input('sort', 'title:asc'));

        foreach ($terms as $term) {
            $suggestions[] = [
                'value' => $term->id(),
                'text'  => $this->label($term, 'title')
            ];
        }

        return $suggestions;
    }
}
