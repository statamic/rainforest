<?php

namespace Statamic\Addons\Crud;

use Statamic\API\Entry;
use Statamic\API\Crypt;
use Statamic\API\Request;
use Statamic\Extend\Listener;

class CrudListener extends Listener
{
    public $events = [
        'Crud.post' => 'post'
    ];

    public function post($type)
    {
        if ($type === 'entry') {
            return $this->createEntry();
        } else {
            // ¯\_(ツ)_/¯ haven't got that far yet.
        }
    }

    private function createEntry()
    {
        $data = Request::all();

        // Get the encrypted set of parameters
        $params = Crypt::decrypt($data['params']);
        unset($data['params']);

        // Remove reserved meta keys from the data and use them as variables.
        $meta = ['slug', 'order'];
        foreach ($meta as $key) {
            $$key = array_get($data, $key);
            unset($data[$key]);
        }

        // Begin building an entry in the factory...
        $factory = Entry::create($slug)
                        ->collection($params['collection'])
                        ->with($data);

        // Mark as draft/unpublished, if specified.
        if (! array_get($params, 'published', true)) {
            $factory->published(false);
        }

        if ($order) {
            $factory->order($order);
        }

        // Grab the completed entry
        $entry = $factory->get();

        // Make sure there's a UUID, and save it!
        $entry->ensureUuid();
        $entry->save();

        return redirect()->back();
    }
}
