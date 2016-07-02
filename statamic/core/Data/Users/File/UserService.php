<?php

namespace Statamic\Data\Users\File;

use Statamic\Data\File\DataService;
use Statamic\Contracts\Data\Users\UserService as UserServiceContract;

/**
 * A layer for interacting with and manipulating users
 */
class UserService extends DataService implements UserServiceContract
{
    /**
     * Get all the users
     *
     * @return \Statamic\Data\UserCollection
     */
    public static function getAll()
    {
        return user_cache()->getAll();
    }

    /**
     * Get a user by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\User
     */
    public static function get($id)
    {
        return user_cache()->getById($id);
    }

    /**
     * Get a user by username
     *
     * @param string $username
     * @return \Statamic\Contracts\Data\User
     */
    public static function getByUsername($username)
    {
        return user_cache()->getByUsername($username);
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

    /**
     * Get a user by their OAuth provider's ID
     *
     * @param string $provider
     * @param mixed $id
     * @return \Statamic\Contracts\Data\User
     */
    public function getByOAuthId($provider, $id)
    {
        return $this->getAll()->filter(function ($user) use ($provider, $id) {
            return $user->getOAuthId($provider) === $id;
        })->first();
    }

    /**
     * Get data by its UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Data\Content
     */
    public function getUuid($uuid, $locale = null)
    {
        return $this->get($uuid);
    }
}
