<?php

namespace Statamic\CP\Publish;

use Carbon\Carbon;
use Statamic\API\Content;
use Statamic\API\TaxonomyTerm;

class TaxonomyPublisher extends Publisher
{
    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $group;

    public function publish()
    {
        $this->group = $this->request->input('extra.group');

        $this->initialValidation();

        // We'll get and prepare the content object. This means we'll retrieve or create it, whatever
        // the case may be. We'll also update the essentials like the slug, status, and order.
        $this->prepare();

//        dd('back at @publish', $this->content);

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

        $messages = [];

        $this->validate($rules, $messages, [
            'fields.title' => $this->getTitleDisplayName(),
            'slug' => 'Slug'
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
            // Creating a brand new taxonomy
            $this->prepForBrandNewTaxonomy();

        } elseif ($this->isNew() && $this->isLocalized()) {
            // Creating a new locale of an existing taxonomy
            $this->prepForNewLocaleForExistingTaxonomy();

        } else {
            // Updating an existing taxonomy
            $this->prepForExistingTaxonomy();
        }
    }



    /**
     * Prepare a brand new entry
     */
    private function prepForBrandNewTaxonomy()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->order = $this->getSubmittedOrderKey();

        $this->status = $this->getSubmittedStatus();

        $this->content = $this->getBrandSpankingNewTaxonomy();
    }

    /**
     * Prepare a new localized version of an existing entry
     */
    private function prepForNewLocaleForExistingTaxonomy()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->content = $this->getNewLocalizedTaxonomy();
    }

    /**
     * Prepare an existing page
     *
     * @throws \Exception
     */
    private function prepForExistingTaxonomy()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->uuid = $this->request->input('uuid');

        $this->content = Content::uuidRaw($this->uuid, $this->locale);

        if (! $this->isLocalized()) {
            // Only the default locale can have its status and order modified
            $this->content->published($this->getSubmittedStatus());
            $this->content->order($this->getSubmittedOrderKey());
        }
    }

    /**
     * Create a brand spankin' new taxonomy
     *
     * @return \Statamic\Data\Taxonomy
     * @throws \Exception
     */
    private function getBrandSpankingNewTaxonomy()
    {
        $taxonomy = TaxonomyTerm::create($this->slug)
            ->taxonomy($this->group)
            ->published($this->status)
            ->order($this->order)
            ->locale($this->locale)
            ->get();

        $taxonomy->ensureId();

        return $taxonomy;
    }

    /**
     * Get an existing non-localized taxonomy and prep it for use in a new locale
     *
     * @return \Statamic\Data\Taxonomy
     */
    private function getNewLocalizedTaxonomy()
    {
        $this->uuid = $this->request->get('uuid');

        $taxonomy = clone TaxonomyTerm::getByUuid($this->uuid);

        $taxonomy->data([]);

        $taxonomy->id($this->uuid);

        $taxonomy->locale($this->locale);

        $taxonomy->slug($this->slug);

        $taxonomy->originalPath($taxonomy->path());

        return $taxonomy;
    }
}
