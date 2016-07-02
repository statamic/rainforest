<?php

namespace Statamic\Contracts\Data;

interface DataService
{
    /**
     * Get data by its UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Data\Content
     */
    public function getUuid($uuid, $locale = null);
}
