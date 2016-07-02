<?php

namespace Statamic\Stache\MySQL;

use Statamic\API\User as UserAPI;
use Statamic\Contracts\Data\User;
use Statamic\Data\UserCollection;
use Statamic\Contracts\Stache\UserCache as UserCacheContract;

class UserCache implements UserCacheContract
{
    /**
     * @return \Statamic\Data\UserCollection
     */
    public function getUsers()
    {
        $results = \DB::select('select * from users');

        return $this->collection($results);
    }

    /**
     * @param string $id
     * @return \Statamic\Data\User
     */
    public function getById($id)
    {
        $results = \DB::select('select * from users where user_id = ?', [$id]);

        return $this->collection($results)->first();
    }

    /**
     * @param string $username
     * @return \Statamic\Contracts\Data\User
     */
    public function getByUsername($username)
    {
        $results = \DB::select('select * from users where username = ?', [$username]);

        return $this->collection($results)->first();
    }

    /**
     * @param string $email
     * @return \Statamic\Contracts\Data\User
     */
    public function getByEmail($email)
    {
        $results = \DB::select('select * from users where email = ?', [$email]);

        return $this->collection($results)->first();
    }

    /**
     * @param string              $key
     * @param \Statamic\Data\User $user
     * @return mixed
     */
    public function setUser($key, User $user)
    {
        // todo
    }

    /**
     * @param array $users
     */
    public function setUsers($users)
    {
        // todo
    }

    /**
     * Takes an array of database results and converts it to a UserCollection
     *
     * @param array $results
     * @return \Statamic\Data\UserCollection
     */
    private function collection($results)
    {
        $users = new UserCollection();

        foreach ($results as $row) {
            $user = UserAPI::create((array) $row);
            $users->put($row->id, $user);
        }

        return $users;
    }
}
