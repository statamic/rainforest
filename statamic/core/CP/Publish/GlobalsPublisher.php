<?php

namespace Statamic\CP\Publish;

use Statamic\API\Content;
use Statamic\API\Globals;

class GlobalsPublisher extends Publisher
{
    /**
     * Publish the page
     *
     * @return \Statamic\Data\Page
     */
    public function publish()
    {
        // We'll get and prepare the content object. This means we'll retrieve or create it, whatever
        // the case may be. We'll also update the essentials like status and order.
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
     * Prepare the content object
     *
     * Retrieve, update, and/or create a Global, depending on the situation.
     */
    private function prepare()
    {
        if ($this->isNew() && !$this->isLocalized()) {
            // Creating a brand new global
            $this->prepForBrandNewGlobal();

        } elseif ($this->isNew() && $this->isLocalized()) {
            // Creating a new locale of an existing global
            $this->prepForNewLocaleForExistingGlobal();

        } else {
            // Updating an existing global
            $this->prepForExistingGlobal();
        }
    }

    /**
     * Prepare a brand new global
     */
    private function prepForBrandNewGlobal()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->content = $this->getBrandSpankingNewGlobal();
    }

    /**
     * Prepare a new localized version of an existing global
     */
    private function prepForNewLocaleForExistingGlobal()
    {
        $this->content = $this->getNewLocalizedGlobal();

        $this->content->id($this->uuid);
    }

    /**
     * Prepare an existing global
     *
     * @throws \Exception
     */
    private function prepForExistingGlobal()
    {
        $this->uuid = $this->request->input('uuid');

        $this->content = Content::uuidRaw($this->uuid, $this->locale);

        // Maintain the fieldset
        $this->fields['fieldset'] = $this->content->get('fieldset');
    }

    /**
     * Create a brand spankin' new global
     *
     * @return \Statamic\Data\GlobalContent
     * @throws \Exception
     */
    private function getBrandSpankingNewGlobal()
    {
        return Globals::create($this->slug)->get();
    }

    /**
     * Get an existing non-localized global and prep it for use in a new locale
     *
     * @return \Statamic\Data\GlobalContent
     */
    protected function getNewLocalizedGlobal()
    {
        $this->uuid = $this->request->get('uuid');

        $global = clone Globals::getByUuid($this->uuid);

        $global->data([]);

        $global->locale($this->locale);

        $global->originalPath($global->path());

        return $global;
    }
}
