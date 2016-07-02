<?php

namespace Statamic\Addons\Suggest\Modes;

use Statamic\API\Str;
use Statamic\API\Page;
use Statamic\API\Content;

class PagesMode extends AbstractMode
{
    public function suggestions()
    {
        $suggestions = [];

        // If a parent has been specified, get it's child pages at
        // the specified depth. Otherwise, just get all pages.
        if ($parent = $this->request->input('parent')) {
            $parent = (Str::startsWith($parent, '/')) ? Page::getByUrl($parent) : Page::getByUuid($parent);
            $pages = $parent->children($this->request->input('depth'));
        } else {
            $pages = Content::pages();
        }

        $pages = $pages->multisort($this->request->input('sort', 'title:asc'));

        foreach ($pages as $page) {
            $suggestions[] = [
                'value' => $page->id(),
                'text'  => $this->label($page, 'title')
            ];
        }

        return $suggestions;
    }
}
