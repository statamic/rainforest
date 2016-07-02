<?php

namespace Statamic\CP\Navigation;

use Statamic\API\User;
use Statamic\API\Assets;
use Statamic\API\Globals;
use Statamic\API\Content;
use Statamic\CP\Navigation\Nav;

class NavFactory
{
    private $nav;

    public function __construct(Nav $nav)
    {
        $this->nav = $nav;
    }

    public function build()
    {
        $this->nav->add($this->buildContentNav());
        $this->nav->add($this->buildToolsNav());
        $this->nav->add($this->buildConfigureNav());

        $this->trim();
    }

    /**
     * Remove any sections that have no children.
     *
     * @return void
     */
    private function trim()
    {
        foreach ($this->nav->children() as $item) {
            if ($item->children()->isEmpty()) {
                $this->nav->remove($item);
            }
        }
    }

    private function buildContentNav()
    {
        $nav = $this->item('content');

        if ($this->access('pages:edit')) {
            $nav->add($this->item('pages')->route('pages'));
        }

        if ($this->access('collections:*:edit')) {
            $nav->add($this->buildCollectionsNav());
        }

        if ($this->access('assets:*:edit')) {
            $nav->add($this->buildAssetsNav());
        }

        if ($this->access('taxonomies:*:edit')) {
            $nav->add($this->buildTaxonomiesNav());
        }

        if ($this->access('globals:*:edit')) {
            $nav->add($this->buildGlobalsNav());
        }

        return $nav;
    }

    private function buildCollectionsNav()
    {
        $nav = $this->item('collections')->route('collections');

        $collections = collect(Content::collections())->filter(function ($collection) {
            return $this->access("collections:{$collection->path()}:edit");
        });

        if (count($collections) > 1) {
            foreach ($collections as $slug => $collection) {
                $nav->add(
                    $this->item("collections:$slug")
                         ->route('entries.show', $slug)
                         ->title($collection->title())
                );
            }
        }

        return $nav;
    }

    private function buildAssetsNav()
    {
        $nav = $this->item('assets')->route('assets');

        $containers = collect(Assets::getContainers())->filter(function ($container) {
            return $this->access("assets:{$container->id()}:edit");
        });

        if (count($containers) > 1) {
            foreach ($containers as $id => $container) {
                $nav->add(
                    $this->item("assets:$id")
                         ->route('assets.browse', $id)
                         ->title($container->title())
                );
            }
        }

        return $nav;
    }

    private function buildTaxonomiesNav()
    {
        $nav = $this->item('taxonomies')->route('taxonomies');

        $taxonomies = collect(Content::taxonomies())->filter(function ($taxonomy) {
            return $this->access("taxonomies:{$taxonomy->path()}:edit");
        });

        if (count($taxonomies) > 1) {
            foreach ($taxonomies as $slug => $taxonomy) {
                $nav->add(
                    $this->item("taxonomies:$slug")
                         ->route('terms.show', $slug)
                         ->title($taxonomy->title())
                );
            }
        }

        return $nav;
    }

    private function buildGlobalsNav()
    {
        $nav = $this->item('globals')->route('globals');

        $globals = Globals::getAll()->filter(function ($set) {
            return $this->access("globals:{$set->slug()}:edit");
        });

        if (count($globals) > 1) {
            foreach ($globals as $set) {
                $nav->add(
                    $this->item("globals:{$set->slug()}")
                         ->url($set->editUrl())
                         ->title($set->title())
                );
            }
        }

        return $nav;
    }

    private function buildToolsNav()
    {
        $nav = $this->item('tools');

        if ($this->access('forms')) {
            $nav->add($this->item('forms')->route('forms'));
        }

        if ($this->access('updater')) {
            $nav->add($this->item('updater')->route('updater'));
        }

        if ($this->access('importer')) {
            $nav->add($this->item('import')->route('import'));
        }

        return $nav;
    }

    private function buildConfigureNav()
    {
        $nav = $this->item('configure');

        if ($this->access('super')) {
            $nav->add($this->item('addons')->route('addons'));
            $nav->add($this->buildConfigureContentNav());
            $nav->add($this->item('fieldsets')->route('fieldsets'));
            $nav->add($this->item('settings')->route('settings'));
        }

        if ($this->access('users:edit')) {
            $nav->add($this->buildUsersNav());
        }

        return $nav;
    }

    private function buildConfigureContentNav()
    {
        $nav = $this->item('config-content')->route('content')->title('Content');

        $nav->add($this->item('assets')->route('assets.containers.manage'));
        $nav->add($this->item('collections')->route('collections.manage'));
        $nav->add($this->item('taxonomies')->route('taxonomies.manage'));
        $nav->add($this->item('globals')->route('globals.manage'));

        return $nav;
    }

    private function buildUsersNav()
    {
        $nav = $this->item('users')->route('users');

        if ($this->access('super')) {
            $nav->add($this->item('user-groups')->route('user.groups')->title('Groups'));
            $nav->add($this->item('user-roles')->route('user.roles')->title('Roles'));
        }

        return $nav;
    }

    private function item($name)
    {
        $item = new NavItem;

        $item->name($name);

        return $item;
    }

    private function access($key)
    {
        if (! User::loggedIn()) {
            return false;
        }

        return User::getCurrent()->can($key);
    }
}
