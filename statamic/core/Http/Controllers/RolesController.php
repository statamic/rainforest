<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Str;
use Statamic\API\Roles;
use Statamic\API\Assets;
use Statamic\API\Helper;
use Statamic\API\Content;
use Statamic\API\Permissions;

class RolesController extends CpController
{
    public function index()
    {
        return view('roles.index', [
            'title' => 'Roles'
        ]);
    }

    public function get()
    {
        $roles = [];

        foreach ($this->getRoles() as $key => $role) {
            $roles[] = [
                'title' => $role->title(),
                'edit_url' => route('user.role', $key),
                'uuid' => $role->uuid(), // @todo: remove.
                'id' => $role->uuid()
            ];
        }

        return ['columns' => ['title'], 'items' => $roles];
    }

    /**
     * @param $role
     * @return \Statamic\Contracts\Permissions\Role
     */
    private function getRole($role)
    {
        return array_get($this->getRoles(), $role);
    }

    /**
     * @return \Statamic\Contracts\Permissions\Role[]
     */
    public function getRoles()
    {
        return Roles::all();
    }

    public function edit($role)
    {
        $role = $this->getRole($role);

        $data = [
            'title' => 'Edit role',
            'role' => $role,
            'content_titles' => $this->getContentTitles(),
            'permissions' => Permissions::structured(),
            'selected' => $this->getPermissions($role)
        ];

        return view('roles.edit', $data);
    }

    private function getContentTitles()
    {
        $titles = [];

        foreach (Content::collections() as $slug => $collection) {
            $titles['collections'][$slug] = $collection->title();
        }

        foreach (Content::taxonomies() as $slug => $taxonomy) {
            $titles['taxonomies'][$slug] = $taxonomy->title();
        }

        foreach (Content::globals() as $global) {
            $titles['globals'][$global->slug()] = $global->title();
        }

        foreach (Assets::getContainers() as $id => $container) {
            $titles['assets'][$id] = $container->title();
        }

        return $titles;
    }

    /**
     * Get an array of permissions that have been added to the given role.
     *
     * @param null|\Statamic\Contracts\Permissions\Role $role
     * @return array
     */
    private function getPermissions($role = null)
    {
        $results = [];

        foreach (Permissions::all() as $permission) {
            if ($role->hasPermission($permission)) {
                $results[] = $permission;
            }
        }

        return $results;
    }

    public function update($role)
    {
        $role = $this->getRole($role);

        $permissions = $this->request->input('permissions', []);

        $role->permissions($permissions);

        $title = $this->request->input('title');
        $role->title($title);
        $role->slug($this->request->input('slug', Str::slug($title)));

        $role->save();

        return redirect()->back()->with('success', 'Role updated.');
    }

    public function create()
    {
        $data = [
            'title' => 'Create role',
            'content_titles' => $this->getContentTitles(),
            'permissions' => Permissions::structured(),
            'selected' => []
        ];

        return view('roles.create', $data);
    }

    public function store()
    {
        $title = $this->request->input('title');

        $data = [
            'title' => $title,
            'permissions' => $this->request->input('permissions', [])
        ];

        $role = app('Statamic\Contracts\Permissions\RoleFactory')->create($data);

        $role->save();

        return redirect()->route('user.role', $role->uuid())->with('success', 'Role updated.');
    }

    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $id) {
            Roles::get($id)->delete();
        }

        return ['success' => true];
    }
}
