<?php

namespace Statamic\API;

class User
{
    /**
     * @return \Statamic\Contracts\Data\Users\UserService
     */
    private static function users()
    {
        return app('Statamic\Contracts\Data\Users\UserService');
    }

    /**
     * Get all users
     *
     * @return \Statamic\Data\UserCollection
     */
    public static function all()
    {
        return self::users()->getAll();
    }

    /**
     * Get a user by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Users\User
     */
    public static function get($id)
    {
        return self::users()->get($id);
    }

    /**
     * Get a user by username
     *
     * @param string $username
     * @return \Statamic\Contracts\Data\User
     */
    public static function username($username)
    {
        return self::users()->getByUsername($username);
    }

    /**
     * Get a user by email
     *
     * @param string $email
     * @return \Statamic\Contracts\Data\User
     */
    public static function email($email)
    {
        return self::users()->getByEmail($email);
    }

    /**
     * Get a user by their oauth provider's id
     *
     * @param string $provider
     * @param string $id
     * @return \Statamic\Contracts\Data\User
     */
    public static function oauth($provider, $id)
    {
        return self::users()->getByOAuthId($provider, $id);
    }

    /**
     * Create a user
     *
     * @return \Statamic\Contracts\Data\Users\UserFactory
     */
    public static function create()
    {
        return app('Statamic\Contracts\Data\Users\UserFactory');
    }

    /**
     * Get the currently authenticated user
     *
     * @return \Statamic\Contracts\Data\Users\User|\Statamic\Contracts\Permissions\Permissible
     */
    public static function getCurrent()
    {
        return request()->user();
    }

    /**
     * Is the user logged in?
     *
     * @return bool
     */
    public static function loggedIn()
    {
        return (bool) self::getCurrent();
    }
}
