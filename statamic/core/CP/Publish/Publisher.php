<?php

namespace Statamic\CP\Publish;

use Statamic\API\Event;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Stache;
use Statamic\API\Str;
use Statamic\API\Fieldset;
use Illuminate\Http\Request;
use Statamic\Exceptions\PublishException;

class Publisher
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Statamic\Contracts\Data\Content\Content
     */
    protected $content;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $order;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * Create a new Publisher
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->fields = $this->request->input('fields');

        $this->locale = $this->request->has('locale') ? $this->request->input('locale') : site_locale();
    }

    /**
     * Get the slug from the submission
     *
     * @return string
     */
    protected function getSubmittedSlug()
    {
        // If there's a slug, use it. Otherwise make one from the title field.
        // If there's no title field, an error should be thrown elsewhere.
        return ($this->request->has('slug'))
               ? $this->request->input('slug')
               : Str::slug($this->request->input('fields.title'));
    }

    /**
     * Get the order key from the submission
     *
     * @return string|null
     */
    protected function getSubmittedOrderKey()
    {
        return ($this->request->has('order'))
            ? $this->request->input('order')
            : null;
    }

    /**
     * Get the status from the submission
     *
     * @return string|null
     */
    protected function getSubmittedStatus()
    {
        return $this->request->input('status');
    }
    
    /**
     * Get the 'display' name of the title field from the fieldset
     * 
     * @return string
     */
    protected function getTitleDisplayName()
    {
        if (! $this->request->has('fieldset')) {
            return trans('cp.title');
        }
        
        $fieldset = Fieldset::get($this->request->input('fieldset'))->contents();
        
        $title = array_get($fieldset, 'fields.title.display', trans('cp.title'));
        
        return $title;
    }

    /**
     * Run field data through fieldtypes processors
     */
    protected function processFields()
    {
        foreach ($this->content->fieldset()->fieldtypes() as $field) {
            if (! in_array($field->getName(), array_keys($this->fields))) {
                continue;
            }

            $this->fields[$field->getName()] = $field->process($this->fields[$field->getName()]);
        }

        // Get rid of null fields
        $this->fields = array_filter($this->fields);
    }

    /**
     * Perform validation with provided rules
     *
     * @param  array $rules
     * @param  array $messages
     * @throws PublishException
     */
    protected function validate($rules, $messages = [], $attributes = [])
    {
        $validator = app('validator')->make($this->request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            $e = new PublishException;
            $e->setErrors($validator->errors()->toArray());
            throw $e;
        }
    }

    /**
     * Get the required fields from the fieldset
     *
     * @return array
     */
    protected function requiredFields()
    {
        $fieldset = Fieldset::get($this->request->input('fieldset'));

        return collect($fieldset->fields())->filter(function ($field) {
            return array_get($field, 'required');
        })->keys()->map(function ($field) {
            return 'fields.' . $field;
        })->all();
    }

    /**
     * Validate the submission and redirect on failure
     *
     * @param array $submission
     */
    protected function validateSubmission($submission)
    {
        $rules = [];
        $attributes = [];

        $validation = new ValidationBuilder($this->fields, $this->content->fieldset());
        $validation->build();

        $this->validate($validation->rules(), [], $validation->attributes());
    }

    /**
     * Update the content object with the data from the submission
     *
     * @throws \Exception
     */
    protected function updateContent()
    {
        $this->fields['id'] = $this->uuid;

        $this->content->data($this->fields);

        if ($this->slug) {
            $this->content->slug($this->slug);
        }
    }

    /**
     * Save the content
     */
    protected function save()
    {
        // Save the content
        $this->content->save();

        // Update the cache
        Stache::update();

        // Fire events that may be useful. We'll fire a generic content published
        // event as well as a specific one for the type of content published.
        Event::fire('cp.published', $this->content);

        if ($this->content instanceof \Statamic\Contracts\Data\Pages\Page) {
            Event::fire('cp.page.published', $this->content);
        } elseif ($this->content instanceof \Statamic\Contracts\Data\Entries\Entry) {
            Event::fire('cp.entry.published', $this->content);
        } elseif ($this->content instanceof \Statamic\Contracts\Data\Taxonomies\Term) {
            Event::fire('cp.term.published', $this->content);
        } elseif ($this->content instanceof \Statamic\Contracts\Data\Globals\GlobalContent) {
            Event::fire('cp.globals.published', $this->content);
        } elseif ($this->content instanceof \Statamic\Contracts\Data\Users\User) {
            Event::fire('cp.user.published', $this->content);
        }
    }

    /**
     * Is this content new?
     *
     * @return bool
     */
    protected function isNew()
    {
        return bool($this->request->input('new'));
    }

    /**
     * Is this content localized? (ie. not the default locale)
     *
     * @return bool
     */
    protected function isLocalized()
    {
        return $this->locale !== Config::getDefaultLocale();
    }
}
