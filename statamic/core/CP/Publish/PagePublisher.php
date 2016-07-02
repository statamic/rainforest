<?php

namespace Statamic\CP\Publish;

use Statamic\API\URL;
use Statamic\API\Page;
use Statamic\API\Content;

class PagePublisher extends Publisher
{
    /**
     * The URL of the current page.
     *
     * @var string
     */
    protected $url;

    /**
     * The URL of the current page, before any modifications through the form.
     *
     * @var string
     */
    protected $original_url;

    /**
     * The URL of the current page's parent.
     *
     * @var string
     */
    protected $parent_url;

    /**
     * The unlocalized url. This is used when editing a localized page.
     *
     * @var string
     */
    protected $default_url;

    /**
     * Publish the page
     *
     * @return \Statamic\Data\Page
     */
    public function publish()
    {
        $this->initialValidation();

        // We'll get and prepare the content object. This means we'll retrieve or create it, whatever
        // the case may be. We'll also update the essentials like status and order.
        $this->prepare();

        // Fieldtypes may modify the values submitted by the user.
        $this->processFields();

        // Update the submission with the modified data
        $submission = array_merge($this->request->all(), ['fields' => $this->fields]);
        $this->validateSubmission($submission);

        // Add the fieldset to the data if it was specified on the fly.
        $this->appendFieldset();

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
        $rules = ['fields.title' => 'required'];

        if ($this->request->input('extra.default_url') !== '/') {
            $rules['slug'] = 'required|alpha_dash';
        }

        $this->validate($rules, [], [
            'fields.title' => $this->getTitleDisplayName(),
            'slug' => trans('cp.slug')
        ]);
    }

    /**
     * Prepare the content object
     *
     * Retrieve, update, and/or create a Page, depending on the situation.
     */
    private function prepare()
    {
        if ($this->isNew() && !$this->isLocalized()) {
            // Creating a brand new page
            $this->prepForBrandNewPage();

        } elseif ($this->isNew() && $this->isLocalized()) {
            // Creating a new locale of an existing page
            $this->prepForNewLocaleForExistingPage();

        } else {
            // Updating an existing page
            $this->prepForExistingPage();
        }
    }

    /**
     * Prepare a brand new page
     */
    private function prepForBrandNewPage()
    {
        $this->parent_url = $this->request->input('extra.parent_url');

        $this->slug = $this->getSubmittedSlug();

        $this->url = URL::assemble($this->parent_url, $this->slug);

        $this->order = $this->getNextPageOrderKey();

        $this->status = $this->getSubmittedStatus();

        $this->content = $this->getBrandSpankingNewPage();

        $this->content->fieldset($this->request->input('fieldset'));
    }

    /**
     * Prepare a new localized version of an existing page
     */
    private function prepForNewLocaleForExistingPage()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->content = $this->getNewLocalizedPage();
    }

    /**
     * Prepare an existing page
     *
     * @throws \Exception
     */
    private function prepForExistingPage()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->content = Page::getByUuid($this->request->input('uuid'), $this->locale);

        $this->uuid = $this->content->id();

        if (! $this->isLocalized()) {
            // Only the default locale can have its status modified
            $this->content->published($this->getSubmittedStatus());
        }
    }

    /**
     * Create a brand spankin' new page
     *
     * @return \Statamic\Data\Page
     * @throws \Exception
     */
    private function getBrandSpankingNewPage()
    {
        return Page::create($this->url)
                   ->published($this->status)
                   ->order($this->order)
                   ->get();
    }

    /**
     * Get an existing non-localized page and prep it for use in a new locale
     *
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    protected function getNewLocalizedPage()
    {
        $this->uuid = $this->request->input('uuid');

        $page = clone Page::getByUuid($this->uuid);

        $page->data([]);

        $page->id($this->uuid);

        $page->locale($this->locale);

        $page->originalPath($page->path());

        return $page;
    }

    /**
     * Get the next available page order key
     *
     * @return null|string
     */
    protected function getNextPageOrderKey()
    {
        return Page::getByUrl($this->parent_url)->children(1)->count() + 1;
    }

    /**
     * Get the slug from the submission
     *
     * @return string
     */
    protected function getSubmittedSlug()
    {
        if ($this->request->input('extra.default_url') === '/') {
            return null;
        } else {
            return parent::getSubmittedSlug();
        }
    }

    /**
     * Append the fieldset to the data if its different from what's in the cascade
     */
    private function appendFieldset()
    {
        $parent = Page::getByUrl($this->request->input('extra.parent_url'));

        $fieldset = $this->request->input('fieldset');

        if ($fieldset !== $parent->fieldset()->name()) {
            $this->fields['fieldset'] = $fieldset;
        }
    }
}
