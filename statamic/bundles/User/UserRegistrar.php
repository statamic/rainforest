<?php

namespace Statamic\Addons\User;

use Validator;
use Statamic\API\User;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Fieldset;
use Illuminate\Http\Request;
use Statamic\CP\Publish\ValidationBuilder;

class UserRegistrar
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Fields to be validated
     *
     * @var array
     */
    protected $fields;

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->fieldset = Fieldset::get('user');
    }

    /**
     * Get the Validator instance
     *
     * @return mixed
     */
    public function validator()
    {
        $builder = $this->validationBuilder();

        return Validator::make(['fields' => $this->fields], $builder->rules(), [], $builder->attributes());
    }

    /**
     * Create the user
     *
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function create()
    {
        $user = User::create()
            ->username($this->request->input('username'))
            ->with($this->userData())
            ->get();

        $user->save();

        return $user;
    }

    /**
     * @return \Statamic\CP\Publish\ValidationBuilder
     */
    protected function validationBuilder()
    {
        $this->adjustFieldset();

        // Remove any unwanted request input to be validated
        $this->fields = $this->request->except('redirect');

        // Build the validation rules/attributes based on the user fieldset
        $builder = new ValidationBuilder(['fields' => $this->fields], $this->fieldset);
        $builder->build();

        return $builder;
    }

    /**
     * Add some additional fields and validation rules to the fieldset
     *
     * @return void
     */
    protected function adjustFieldset()
    {
        $fields = $this->fieldset->fields();

        array_set($fields, 'email.validate', 'required|email');

        array_set($fields, 'username.validate', 'required');

        array_set($fields, 'password', [
            'display' => trans_choice('cp.passwords', 1),
            'validate' => 'required|confirmed'
        ]);

        $this->fieldset->fields($fields);
    }

    /**
     * Get the data to be stored in the user
     *
     * @return mixed
     */
    protected function userData()
    {
        // We're mapping to null values and filtering here because
        // ->filter() doesn't pass along the keys in Laravel 5.1.
        $data = collect($this->fields)->map(function ($value, $key) {
            return (in_array($key, $this->whitelistedFields())) ? $value : null;
        })->filter()->all();

        if ($roles = Config::get('users.new_user_roles')) {
            $data['roles'] = Helper::ensureArray($roles);
        }

        return $data;
    }

    /**
     * Get the fields that shouldn't be added to a user
     *
     * @return array
     */
    protected function blacklistedFields()
    {
        return ['username', 'roles'];
    }

    /**
     * Get the fields that are allowed to be added to a user
     *
     * @return array
     */
    protected function whitelistedFields()
    {
        return array_diff(array_keys($this->fieldset->fields()), $this->blacklistedFields());
    }
}