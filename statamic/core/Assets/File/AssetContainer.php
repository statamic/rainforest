<?php

namespace Statamic\Assets\File;

use Statamic\API\Path;
use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\YAML;
use Statamic\API\Storage;
use Statamic\API\Folder;
use Statamic\Assets\AssetContainer as BaseAssetContainer;

class AssetContainer extends BaseAssetContainer
{
    /**
     * Get or set the handle
     *
     * @param null|string $handle
     * @return string
     */
    public function handle($handle = null)
    {
        // For files, the id is also the handle.
        return $this->id($handle);
    }

    /**
     * Save the container
     */
    public function save()
    {
        $path = 'assets/' . $this->uuid . '/container.yaml';

        $data = array_filter($this->toArray());
        unset($data['id']);
        $yaml = YAML::dump($data);

        Storage::put($path, $yaml);

        // Create an empty folder if one doesn't exist.
        $folder_path = 'assets/' . $this->uuid . '/folder.yaml';
        if (! Storage::exists($folder_path)) {
            $yaml = YAML::dump([
                'title' => $this->title,
                'assets' => []
            ]);

            Storage::put($folder_path, $yaml);
        }
    }

    /**
     * Delete the container
     *
     * @return mixed
     */
    public function delete()
    {
        Folder::disk('storage')->delete('assets/' . $this->uuid);
    }
}
