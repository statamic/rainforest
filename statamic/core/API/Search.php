<?php

namespace Statamic\API;

class Search
{
    /**
     * Search instance
     *
     * @return \Statamic\Contracts\Search\Search
     */
    private static function search()
    {
        return app('Statamic\Contracts\Search\Search');
    }

    /**
     * Initiate a search query
     *
     * @param  string $query String to search
     * @param  array|null $fields Fields to search in, or null to search all fields
     * @return Query
     */
    public static function query($query, $fields = null)
    {
        return self::search()->search($query, $fields);
    }

    /**
     * Perform a search and get the results
     *
     * @param  string $query String to search
     * @param  array|null $fields Fields to search in, or null to search all fields
     * @return array
     */
    public static function get($query, $fields = null)
    {
        return self::query($query, $fields)->get();
    }

    /**
     * Get a search index
     *
     * @param  string $index Name of the index
     * @return Index
     */
    public static function in($index)
    {
        return self::search()->index($index);
    }

    /**
     * Update a search index
     *
     * @param  string $index Name of the index
     * @return void
     */
    public static function update($index = null)
    {
        return self::search()->update($index);
    }
}
