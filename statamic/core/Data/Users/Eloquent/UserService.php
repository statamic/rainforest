<?php

namespace Statamic\Data\Eloquent;

use Statamic\Data\UserCollection;
use Statamic\Contracts\Data\UserService as UserServiceContract;

/**
 * A layer for interacting with and manipulating users
 */
class UserService implements UserServiceContract
{
    /**
     * Create a new User object
     *
     * @param array $attributes
     * @return \Statamic\Contracts\Data\User
     */
    public static function create($attributes)
    {
        $attributes['password'] = bcrypt($attributes['password']);

        return new User($attributes);
    }

    /**
     * Get all the users
     *
     * @return \Statamic\Data\UserCollection
     */
    public static function getAll()
    {
        return new UserCollection(User::all()->toArray());
    }

    /**
     * Get a user by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\User
     */
    public static function get($id)
    {
        // TODO: Implement get() method.
    }

    /**
     * Get a user by username
     *
     * @param string $username
     * @return \Statamic\Contracts\Data\User
     */
    public static function getByUsername($username)
    {
        // TODO: Implement getByUsername() method.
    }

    /**
     * Get a user by email
     *
     * @param string $email
     * @return \Statamic\Contracts\Data\User
     */
    public static function getByEmail($email)
    {
        return user_cache()->getByEmail($email);
    }
}
