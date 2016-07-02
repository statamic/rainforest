<?php

namespace Statamic\CP\Publish;

use Statamic\API\User;
use Statamic\API\Config;
use Statamic\API\Helper;

class UserPublisher extends Publisher
{
    protected $login_type;

    /**
     * Publish the entry
     *
     * @return \Statamic\Contracts\Data\
     */
    public function publish()
    {
        // We'll get and prepare the content object. This means we'll retrieve or create it, whatever
        // the case may be. We'll also update the essentials like the slug, status, and order.
        $this->prepare();

//        dd('back at @publish', $this->content);

        // Fieldtypes may modify the values submitted by the user.
        $this->processFields();

        // Update the submission with the modified data
//        $submission = array_merge($this->request->all(), ['fields' => $this->fields]);
//        $this->validateSubmission($submission);

        // Commit any changes made by the user and/or the fieldtype processors back to the content object.
        $this->updateContent();

        // Save the file and any run any supplementary tasks like updating the cache, firing events, etc.
        $this->save();

        return $this->content;
    }

    /**
     * Prepare the content object
     *
     * Retrieve, update, and/or create an Entry, depending on the situation.
     */
    private function prepare()
    {
        $this->login_type = Config::get('users.login_type');

        $username = array_get($this->fields, 'username');
        $email    = array_get($this->fields, 'email');
        $groups   = array_get($this->fields, 'user_groups', []);
        unset($this->fields['username'], $this->fields['user_groups'], $this->fields['status']);

        if ($this->isNew()) {
            // Creating a brand new user
            if ($this->login_type === 'email') {
                $this->content = User::create()->email($email)->get();
            } else {
                $this->content = User::create()->username($username)->get();
            }

            // Set the ID now because the $user->groups() method relies on it
            $this->uuid = Helper::makeUuid();
            $this->content->id($this->uuid);

        } else {
            // Updating an existing user
            $this->prepForExistingUser();

            if ($this->login_type === 'username') {
                $this->content->username($username);
            }
        }

        $this->content->groups($groups);
    }

    /**
     * Prepare an existing user
     *
     * @throws \Exception
     */
    private function prepForExistingUser()
    {
        $this->uuid = $this->request->input('uuid');

        $this->content = User::get($this->uuid);
    }
}
