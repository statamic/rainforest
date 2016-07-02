<?php

namespace Statamic\Addons\Cache;

use Statamic\Extend\Controller;

class CacheController extends Controller
{
    public function index()
    {
        return $this->view('index', ['title' => 'Cache']);
    }
}
