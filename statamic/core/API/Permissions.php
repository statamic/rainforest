<?php

namespace Statamic\API;

class Permissions
{
    public static function all($wildcards = false)
    {
        return app('permissions')->all($wildcards);
    }

    public static function structured()
    {
        return app('permissions')->structured();
    }
}
