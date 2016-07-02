<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Stache;
use Statamic\API\User;
use Statamic\API\Assets;
use Statamic\API\Helper;

class AssetContainersController extends CpController
{
    public function index()
    {
        $this->access('assets:*:edit');

        $containers = collect(Assets::getContainers())->filter(function ($container) {
            return User::getCurrent()->can("assets:{$container->uuid()}:edit");
        })->all();

        if (count($containers) === 1) {
            return redirect()->route('assets.browse', reset($containers)->id());
        }

        return view('assets.containers.index', [
            'title' => 'Assets'
        ]);
    }

    public function manage()
    {
        return view('assets.containers.manage', [
            'title' => 'Assets'
        ]);
    }

    public function get()
    {
        $containers = [];

        foreach (Assets::getContainers() as $container) {
            if (! User::getCurrent()->can("assets:{$container->uuid()}:edit")) {
                continue;
            }

            $containers[] = [
                'id' => $container->uuid(),
                'title' => $container->title(),
                'assets' => $container->assets()->count(),
                'edit_url'    => $container->editUrl(),
                'browse_url' => route('assets.browse', $container->uuid())
            ];
        }

        return ['columns' => ['title'], 'items' => $containers];
    }

    public function create()
    {
        return view('assets.containers.create', [
            'title' => 'Creating Asset Container'
        ]);
    }

    public function store()
    {
        $handle = $this->request->input('handle');

        if (Assets::getContainer($handle)) {
            return [
                'success' => false,
                'errors' => ['A container with this handle already exists.']
            ];
        }

        $container = Assets::createContainer();

        $container->handle($handle);

        return $this->save($container);
    }

    public function edit($uuid)
    {
        $container = Assets::getContainer($uuid);

        return view('assets.containers.edit', [
            'title' => 'Editing Container',
            'container' => $container
        ]);
    }

    public function update($uuid)
    {
        $container = Assets::getContainer($uuid);

        return $this->save($container);
    }

    private function save($container)
    {
        $driver = $this->request->input('driver');

        $config = $this->request->input($driver);

        $data = [
            'driver' => $driver,
            'title' => $this->request->input('title'),
            'fieldset' => $this->request->input('fieldset'),
        ];

        $data = array_merge($config, $data);

        $container->data($data);

        $container->save();

        $this->success('Container saved');

        return [
            'success' => true,
            'redirect' => route('assets.container.edit', $container->uuid())
        ];
    }

    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $id) {
            Assets::getContainer($id)->delete();
        }

        return ['success' => true];
    }

    public function folders($container)
    {
        $container = Assets::getContainer($container);

        return collect($container->folders())->map(function ($folder) {
            return $folder->title();
        });
    }

    public function sync($container)
    {
        $container = Assets::getContainer($container);

        $assets = $container->sync();

        return [
            'success' => true,
            'synced' => $assets->toArray(),
        ];
    }
}
