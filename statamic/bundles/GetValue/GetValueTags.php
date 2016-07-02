<?php

namespace Statamic\Addons\GetValue;

use Statamic\API\Content;
use Statamic\Extend\Tags;

class GetValueTags extends Tags
{
    /**
     * Maps to {{ get_value:[field] }}
     *
     * @param  string $method
     * @param  array  $arguments
     * @return string
     */
    public function __call($method, $arguments)
    {
        $content = $this->context;

        if ($from = $this->get('from')) {
            $content = Content::get($from);
            unset($filters['from']);
        }

        if (! $field_data = array_get($content, $method)) {
            return $this->noResults();
        }

        $values = $field_data;

        if ($filters = $this->parameters) {
            $values = array_values(array_filter($field_data, function ($i) use ($filters) {
                foreach ($filters as $key => $val) {
                    $match = array_get($i, $key) == $val;

                    if (! $match) {
                        break;
                    }
                }

                return $match;
            }));
        }

        if (empty($values)) {
            return $this->noResults();
        }

        return $this->parseLoop($values);
    }

    /**
     * Output no results
     *
     * @return array
     */
    private function noResults()
    {
        return ['no_results' => true];
    }
}
