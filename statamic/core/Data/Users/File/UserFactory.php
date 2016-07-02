<?php

namespace Statamic\Data\Users\File;

use Statamic\Contracts\Data\Users\UserFactory as UserFactoryContract;

class UserFactory implements UserFactoryContract
{
    protected $data = [];
    protected $username;
    protected $email;

    /**
     * @return $this
     */
    public function create()
    {
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function username($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function email($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function get()
    {
        $user = new User($this->data);

        $user->username($this->username);
        $user->originalUsername($this->username);

        if ($this->email) {
            $user->email($this->email);
        }

        return $user;
    }
}
