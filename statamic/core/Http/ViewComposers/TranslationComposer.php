<?php

namespace Statamic\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Statamic\API\Folder;

class TranslationComposer
{
    public function compose(View $view)
    {
        $translations = $this->getTranslations();

        $view->with('translations', json_encode($translations));
    }

    private function getTranslations()
    {
        $messages = [];
        $path = base_path() . '/resources/lang/' . site_locale();

        foreach (Folder::getFiles($path) as $file) {
            $pathinfo = pathinfo($file);

            if ($pathinfo['extension'] !== 'php') {
                continue;
            }

            $key = str_replace('\\', '.', $pathinfo['filename']);
            $key = str_replace('/', '.', $key);

            $messages[site_locale() . '.' . $key] = include root_path($file);
        }

        return $messages;
    }
}
