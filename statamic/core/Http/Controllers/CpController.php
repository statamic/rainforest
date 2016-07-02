<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Nav;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\User;
use Statamic\API\Cache;
use Statamic\API\Assets;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\API\Content;
use Statamic\API\Globals;
use Illuminate\Http\Request;

/**
 * The base control panel controller
 */
class CpController extends Controller
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new CpController
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        // Set the locale the translator will use. Allow users to set from
        // their own account, and fall back to the default site locale.
        $locale = site_locale();
        $locale = (\Auth::check()) ? User::getCurrent()->get('locale', $locale) : $locale;
        \Lang::setLocale($locale);

        $this->request = $request;
    }

    /**
     * 404
     */
    public function pageNotFound()
    {
        abort(404);
    }

    /**
     * Save some flash data to the session
     *
     * @param string $key
     * @param string $value
     */
    protected function flash($key, $value)
    {
        $this->request->session()->flash($key, $value);
    }

    /**
     * Set the successful flash message
     *
     * @param string $message
     * @param null   $text
     * @return array
     */
    protected function success($message, $text = null)
    {
        $this->flash('success', $message);

        if ($text) {
            $this->flash('success_text', $text);
        }
    }

    /**
     * Get all the template names from the current theme
     *
     * @return array
     */
    public function templates()
    {
        $templates = [];

        foreach (Folder::disk('theme')->getFilesRecursively('templates') as $path) {
            $parts = explode('/', $path);
            array_shift($parts);
            $templates[] = Str::removeRight(join('/', $parts), '.html');
        }

        return $templates;
    }

    public function themes()
    {
        $themes = [];

        foreach (Folder::disk('themes')->getFolders('/') as $folder) {
            $name = $folder;

            // Get the name if one exists in a meta file
            if (File::disk('themes')->exists($folder.'/meta.yaml')) {
                $meta = YAML::parse(File::disk('themes')->get($folder.'/meta.yaml'));
                $name = array_get($meta, 'name', $folder);
            }

            $themes[] = compact('folder', 'name');
        }

        return $themes;
    }
}
