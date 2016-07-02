<?php

namespace Statamic\Contracts\Stache;

interface Cache
{
    /**
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function getContent();

    /**
     * @param \Statamic\Contracts\Stache\ContentCache $cache
     */
    public function setContent(ContentCache $cache);

    /**
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function getUsers();

    /**
     * @param \Statamic\Contracts\Stache\UserCache $cache
     */
    public function setUsers(UserCache $cache);

    /**
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function getAssets();

    /**
     * @param \Statamic\Contracts\Stache\AssetCache $cache
     */
    public function setAssets(AssetCache $cache);
}
