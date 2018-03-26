<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Package;

use Application\DeskPRO\Entity\AppPackage;
use Symfony\Component\Finder\Finder;

class Package
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var Manifest
     */
    private $manifest;

    public function __construct($path)
    {
        $this->path = rtrim($path, '/');

        $reader = ManifestReader::newFromFile($path.'/manifest.json');
        if ($reader->isError()) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid manifest %s: %s %s',
                $path,
                $reader->getErrorCode(),
                $reader->getErrorDetailAsString()
            ));
        }
        $this->manifest = $reader->getManifest();
    }

    /**
     * @param AppPackage $def         Existing app package to update. Otherwise, a new package is created
     * @param string     $native_name
     *
     * @return AppPackage
     */
    public function createAppPackage(AppPackage $def = null)
    {
        if (!$def) {
            $def = new AppPackage();
        }

        $def->name           = $this->manifest->getPackageName();
        $def->title          = $this->manifest->getTitle();
        $def->description    = $this->manifest->getDescription();
        $def->author_name    = $this->manifest->getAuthorName();
        $def->author_email   = $this->manifest->getAuthorEmail();
        $def->author_link    = $this->manifest->getAuthorLink();
        $def->api_version    = $this->manifest->getApiVersion();
        $def->version        = $this->manifest->getVersion();
        $def->version_name   = $this->manifest->getVersionName();
        $def->is_single      = $this->manifest->getIsSingle();
        $def->scopes         = [AppPackage::SCOPE_AGENT];
        $def->tags           = $this->manifest->getTags() ?: [];
        $def->trigger_events = $this->manifest->getTriggerEvents();
        $def->settings_def   = $this->manifest->getSettingsDef();
        $def->native_name    = $this->manifest->getIsNative() ? $def->name : null;

        return $def;
    }

    /**
     * @return Manifest
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param int $size The size of the icon we want
     *
     * @return string
     */
    public function getIconFilePath($size)
    {
        $path = $this->path.'/res/icons/app_'.$size.'.png';
        if (!file_exists($path)) {
            return;
        }

        return $path;
    }

    /**
     * @return string
     */
    public function getReadmeFilePath()
    {
        $path = $this->path.'/README';
        if (!file_exists($path)) {
            return;
        }

        return $path;
    }

    /**
     * @return string|null
     */
    public function getAppJsFilePath()
    {
        $path = $this->path.'/app.js';
        if (!file_exists($path)) {
            return;
        }

        return $path;
    }

    /**
     * @return string|null
     */
    public function getModuleJsFilePath()
    {
        $path = $this->path.'/module.js';
        if (!file_exists($path)) {
            return;
        }

        return $path;
    }

    /**
     * @return array
     */
    public function getJsAssets()
    {
        return $this->readAssetPath('js');
    }

    /**
     * @return array
     */
    public function getCssAssets()
    {
        return $this->readAssetPath('css');
    }

    /**
     * @return array
     */
    public function getHtmlAssets()
    {
        return $this->readAssetPath('html');
    }

    /**
     * @return array
     */
    public function getResAssets()
    {
        return $this->readAssetPath('res');
    }

    /**
     * @param string $path_name
     *
     * @return array
     */
    private function readAssetPath($path_name)
    {
        $assets   = [];
        $path     = @realpath($this->path.'/'.$path_name);
        $path_std = str_replace('\\', '/', $path);

        if (!$path || !is_dir($path)) {
            return [];
        }

        $finder = Finder::create()->in($path)->files();
        switch ($path_name) {
            case 'js':   $finder->name('*.js'); break;
            case 'html': $finder->name('*.html'); break;
            case 'css':  $finder->name('*.css'); break;
        }

        foreach ($finder as $file) {
            /* @var $file \SplFileInfo */

            $full_path  = $file->getRealPath();
            $asset_path = str_replace($path_std.'/', '', str_replace('\\', '/', $full_path));
            $file_name  = $file->getFilename();

            if ($path_name == 'res') {
                if (preg_match('#res/icons/app_\d+\.png$#', $full_path)) {
                    continue;
                }
            }

            $assets[] = [
                'real_path' => $full_path,
                'path'      => $asset_path,
                'name'      => $file_name,
            ];
        }

        return $assets;
    }
}
