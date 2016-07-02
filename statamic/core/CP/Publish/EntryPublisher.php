<?php

namespace Statamic\CP\Publish;

use Carbon\Carbon;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Entry;
use Statamic\API\Content;
use Statamic\API\Entries;

class EntryPublisher extends Publisher
{
    /**
     * @var string
     */
    protected $collection;

    /**
     * Publish the entry
     *
     * @return \Statamic\Data\Entry
     */
    public function publish()
    {
        $this->collection = $this->request->input('extra.collection');

        $this->initialValidation();

        // We'll get and prepare the content object. This means we'll retrieve or create it, whatever
        // the case may be. We'll also update the essentials like the slug, status, and order.
        $this->prepare();

        // Fieldtypes may modify the values submitted by the user.
        $this->processFields();

        // Update the submission with the modified data
        $submission = array_merge($this->request->all(), ['fields' => $this->fields]);
        $this->validateSubmission($submission);

        // Commit any changes made by the user and/or the fieldtype processors back to the content object.
        $this->updateContent();

        // Save the file and any run any supplementary tasks like updating the cache, firing events, etc.
        $this->save();

        return $this->content;
    }

    /**
     * Perform initial validation
     *
     * @throws \Statamic\Exceptions\PublishException
     */
    private function initialValidation()
    {
        $rules = [
            'fields.title' => 'required',
            'slug' => 'required|alpha_dash'
        ];

        if ($this->getEntryOrderType() === 'date') {
            // 24 hour validation, hat tip to:
            // http://www.mkyong.com/regular-expressions/how-to-validate-time-in-24-hours-format-with-regular-expression/
            $rules['extra.datetime'] = ['required', 'regex:/^\d{4}-\d{2}-\d{2}(?: ([01][0-9]|2[0-3]):[0-5][0-9])?$/'];
        }

        $messages = [
            'extra.datetime.regex' => 'The Date/time field must be a valid 24 hour time (HH:MM).'
        ];

        $this->validate($rules, $messages, [
            'fields.title' => $this->getTitleDisplayName(),
            'slug' => 'Slug',
            'extra.datetime' => 'Date/Time'
        ]);
    }

    /**
     * Prepare the content object
     *
     * Retrieve, update, and/or create an Entry, depending on the situation.
     */
    private function prepare()
    {
        if ($this->isNew() && !$this->isLocalized()) {
            // Creating a brand new entry
            $this->prepForBrandNewEntry();

        } elseif ($this->isNew() && $this->isLocalized()) {
            // Creating a new locale of an existing entry
            $this->prepForNewLocaleForExistingEntry();

        } else {
            // Updating an existing entry
            $this->prepForExistingEntry();
        }
    }

    /**
     * Prepare a brand new entry
     */
    private function prepForBrandNewEntry()
    {
        $this->slug = $this->getSubmittedSlug();

        if (! $this->order = $this->getSubmittedOrderKey()) {
            $this->order = $this->getNewEntryOrderKey();
        }

        $this->status = $this->getSubmittedStatus();

        $this->content = $this->getBrandSpankingNewEntry();
    }

    /**
     * Prepare a new localized version of an existing entry
     */
    private function prepForNewLocaleForExistingEntry()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->content = $this->getNewLocalizedEntry();
    }

    /**
     * Prepare an existing page
     *
     * @throws \Exception
     */
    private function prepForExistingEntry()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->uuid = $this->request->input('uuid');

        $this->content = Content::uuidRaw($this->uuid, $this->locale);

        if (! $this->isLocalized()) {
            // Only the default locale can have its status and order modified
            $this->content->published($this->getSubmittedStatus());

            // If no order was submitted (in the case of numeric
            // entries), we want to get the existing order key.
            if (! $order = $this->getSubmittedOrderKey()) {
                $order = $this->content->order();
            }

            $this->content->order($order);
        }
    }

    /**
     * Create a brand spankin' new entry
     *
     * @return \Statamic\Data\Entry
     * @throws \Exception
     */
    private function getBrandSpankingNewEntry()
    {
        $entry = Entry::create($this->slug)
                    ->collection($this->collection)
                    ->published($this->status)
                    ->order($this->order)
                    ->locale($this->locale)
                    ->get();

        $entry->ensureId();

        return $entry;
    }

    /**
     * Get an existing non-localized entry and prep it for use in a new locale
     *
     * @return \Statamic\Data\Entry
     */
    private function getNewLocalizedEntry()
    {
        $this->uuid = $this->request->input('uuid');

        $entry = clone Entry::getByUuid($this->uuid);

        $entry->data([]);

        $entry->id($this->uuid);

        $entry->locale($this->locale);

        $entry->slug($this->slug);

        $entry->originalPath($entry->path());

        return $entry;
    }

    /**
     * Get the entry order key
     *
     * @return null|string
     */
    protected function getOrderKey()
    {
        if ($this->order) {
            return $this->order;
        }

        $yaml = YAML::parse(File::disk('content')->get('collections/' . $this->collection . '/folder.yaml'));
        $order = array_get($yaml, 'order');

        if ($order == 'date') {
            return Carbon::now()->format('Y-m-d');
        }

        if ($order == 'number') {
            return Entries::getFromCollection($this->collection)->count() + 1;
        }

        return null;
    }

    protected function getSubmittedOrderKey()
    {
        // If it's not a date, you can't choose the order of an entry while publishing.
        if ($this->getEntryOrderType() !== 'date') {
            return null;
        }

        $date = $this->request->input('extra.datetime');

        // If there's a time, adjust the format into a datetime order string.
        if (strlen($date) > 10) {
            $date = str_replace(':', '', $date);
            $date = str_replace(' ', '-', $date);
        }

        return $date;
    }

    private function getEntryOrderType()
    {
        // Get the entry order type from either the content if it exists, or from the POST for a new entry.
        return ($this->content)
            ? $this->content->orderType()
            : $this->request->input('extra.order_type');
    }

    private function getNewEntryOrderKey()
    {
        $order_type = $this->getEntryOrderType();

        if ($order_type === 'date') {
            return Carbon::now()->format('Y-m-d');
        }

        if ($order_type === 'number') {
            return Entries::getFromCollection($this->collection)->count() + 1;
        }
    }
}
