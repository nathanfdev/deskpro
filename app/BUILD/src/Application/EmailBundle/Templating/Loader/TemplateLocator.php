<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Templating\Loader;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator as BaseTemplateLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

class TemplateLocator extends BaseTemplateLocator
{
    /** @var \Symfony\Component\Config\FileLocatorInterface */
    protected $locator;
    /** @var array */
    protected $cache = [];
    /** @var array */
    protected $loaded_list = [];

    public function __construct(FileLocatorInterface $locator, $cacheDir = null)
    {
        $cache_file = DP_ROOT.'/sys/template-map.php';
        if (is_file($cache_file)) {
            $this->cache = require $cache_file;
        }

        $this->locator = $locator;
    }

    public function locate($template, $currentPath = null, $first = true)
    {
        if (!$template instanceof TemplateReferenceInterface) {
            throw new \InvalidArgumentException('The template must be an instance of TemplateReferenceInterface.');
        }

        $key = $template->getLogicalName();

        if (isset($this->cache[$key])) {
            $this->logUsedTemplate($key, $this->cache[$key]['path']);

            return $this->cache[$key]['path'];
        }

        // App views
        try {
            $bundle = $template->get('bundle');
        } catch (\InvalidArgumentException $e) {
            $bundle = null;
        }
        if (!$bundle) {
            $tpl   = ltrim($key, ':');
            $parts = explode(':', $tpl, 2);
            if (isset($parts[1])) {
                $native_name = $parts[0];
                $file_name   = $parts[1];

                $path = DP_ROOT.'/apps/'.$native_name.'/native/Resources/views/'.ltrim($file_name, '/');
                if (file_exists($path)) {
                    $this->cache[$key] = ['path' => $path];

                    return $path;
                }
            }
        }

        try {
            $this->cache[$key] = [
                'path' => $this->locator->locate($template->getPath(), $currentPath),
            ];
            $this->logUsedTemplate($key, $this->cache[$key]['path']);

            return $this->cache[$key]['path'];
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Unable to find template "%s" : "%s".', $template, $e->getMessage()), 0, $e);
        }
    }

    protected function logUsedTemplate($key, $path)
    {
        $this->loaded_list[] = [
            'key'    => $key,
            'path'   => $path,
            'origin' => null,
        ];
    }

    public function getLoadedTemplates()
    {
        return $this->loaded_list;
    }
}
