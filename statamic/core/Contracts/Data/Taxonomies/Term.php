<?php

namespace Statamic\Contracts\Data\Taxonomies;

use Statamic\Contracts\Data\Content\Content;

interface Term extends Content
{
    /**
     * The taxonomy to which this term belongs
     *
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function taxonomy();

    /**
     * The name of the taxonomy to which this term belongs
     *
     * @param string|null $taxonomy
     * @return string
     */
    public function taxonomyName($taxonomy = null);

    /**
     * Get or set content that is related to this term
     *
     * @param \Statamic\Data\Content\ContentCollection|null $collection
     * @return \Statamic\Data\Content\ContentCollection
     */
    public function collection($collection = null);

    /**
     * Get the number of content objects that related to this term
     *
     * @return int
     */
    public function count();
}
