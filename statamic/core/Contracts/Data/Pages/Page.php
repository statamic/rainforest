<?php

namespace Statamic\Contracts\Data\Pages;

use Statamic\Contracts\Data\Content\Content;

interface Page extends Content
{
    /**
     * @param null $path
     * @return mixed
     */
    public function parentPath($path = null);

    /**
     * @return mixed
     */
    public function children();

    /**
     * @return mixed
     */
    public function hasEntries();

    /**
     * @return mixed
     */
    public function entries();

    /**
     * @return mixed
     */
    public function entriesCollection();
}
