<?php

namespace Statamic\Contracts\Data\Users;

interface UserService
{
    /**
     * Get all the users
     *
     * @return \Statamic\Data\UserCollection
     */
    public static function getAll();

    /**
     * Get a user by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\User
     */
    public static function get($id);

    /**
     * Get a user by username
     *
     * @param string $username
     * @return \Statamic\Contracts\Data\User
     */
    public static function getByUsername($username);

    /**
     * Get a user by email
     *
     * @param string $email
     * @return \Statamic\Contracts\Data\User
     */
    public static function getByEmail($email);

    /**
     * Get a user by their OAuth provider's ID
     *
     * @param string $provider
     * @param mixed $id
     * @return \Statamic\Contracts\Data\User
     */
    public function getByOAuthId($provider, $id);
}
