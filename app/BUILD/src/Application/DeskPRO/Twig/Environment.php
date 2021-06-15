<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Twig;

use Application\DeskPRO\Twig\Loader\HybridLoader;
use Twig\Cache\CacheInterface;
use Application\DeskPRO\Templating\TemplateUtils;

class Environment extends \Twig_Environment
{
    /** @var bool */
    protected $ext_dirty = false;

    public function __construct(\Twig_LoaderInterface $loader = null, $options = [])
    {
        static $has_done = false;

        if (defined('DP_DEBUG') && (empty($options['auto_reload']) || $options['auto_reload'] === null)) {
            if (DP_DEBUG) {
                $options['auto_reload'] = true;
            } else {
                $options['auto_reload'] = false;
            }
        }

        if (!$has_done) {
            if (!in_array('dptpl', stream_get_wrappers())) {
                stream_wrapper_register('dptpl', 'Application\\DeskPRO\\Twig\\Loader\\DbStreamWrapper', 0);
                $has_done = true;
            }
            $has_done = true;
        }

        $options['base_template_class'] = '\\Application\\DeskPRO\\Twig\\Template';

        parent::__construct($loader, $options);
    }

    public function setCache($cache)
    {
        if (\is_string($cache)) {
            parent::setCache(new FilesystemCache($cache));

            return;
        }

        parent::setCache($cache);
    }

    public function addExtension(\Twig_ExtensionInterface $extension)
    {
        parent::addExtension($extension);
        $this->ext_dirty = true;
    }

    public function setExtensions(array $extensions)
    {
        parent::setExtensions($extensions);
        $this->ext_dirty = true;
    }

    public function getExtensions()
    {
        // Ensures the DeskPRO filters and functions are always used over the default
        if ($this->ext_dirty) {
            $this->ext_dirty = false;

            $set_ext    = [];
            $append_ext = [];

            foreach ($this->extensions as $k => $ext) {
                if ($ext instanceof \Application\DeskPRO\Twig\Extension\TemplatingExtension || $ext instanceof \Application\UserBundle\Twig\Extension\UserTemplatingExtension) {
                    $append_ext[$k] = $ext;
                } else {
                    $set_ext[$k] = $ext;
                }
            }
            foreach ($append_ext as $k => $ext) {
                $set_ext[$k] = $ext;
            }

            $this->extensions = $set_ext;
        }

        return $this->extensions;
    }

    public function loadTemplate($name, $index = null)
    {
        if (!TemplateUtils::isAllowedTemplateFilepath($name)) {
            throw new \RuntimeException("Not allowed to load template {$name}");
        }

        $name_str = (string) $name;
        if (!$this->isCustomTemplate($name_str)) {
            return $this->doLoadTemplate($name, $index);
        } else {
            try {
                return $this->doLoadTemplate($name, $index);
            } catch (\Exception $e) {
                $errinfo                  = \DpSys\LowError\SystemErrorHandler::getExceptionInfo($e);
                $errinfo['no_send_error'] = true;
                \DpSys\LowError\SystemErrorHandler::noDisplayNextError();
                \DpSys\LowError\SystemErrorHandler::logErrorInfo($errinfo);

                $this->markCustomTemplateAsCrashed($name_str);

                return $this->loadTemplate($name, $index);
            }
        }
    }

    private function doLoadTemplate($name, $index = null)
    {
        if (!isset($GLOBALS['DP_RENDERED_TEMPLATES'])) {
            $GLOBALS['DP_RENDERED_TEMPLATES'] = [];
        }

        $GLOBALS['DP_RENDERED_TEMPLATES'][(string) $name] = true;

        $cls = $this->getTemplateClass($name, $index);

        if (isset($this->loadedTemplates[$cls])) {
            return $this->loadedTemplates[$cls];
        }

        if (method_exists($this->loader, 'isCrashedTemplate') && $this->loader->isCrashedTemplate($name)) {
            $originalClass = $this->getTemplateClass($name);
            $defaultClass  = "{$originalClass}_default";

            if (isset($this->loadedTemplates[$defaultClass])) {
                return $this->loadedTemplates[$defaultClass];
            }

            try {
                $source = $this->compileSource($this->loader->getSourceContext($name), $name);
                $source = str_replace($originalClass, $defaultClass, $source);

                if (!class_exists($defaultClass, false)) {
                    eval('?>'.$source);
                }

                if (!$this->runtimeInitialized) {
                    $this->initRuntime();
                }

                return $this->loadedTemplates[$defaultClass] = new $defaultClass($this);
            } catch (\Exception $e) {
                $errinfo                  = \DpSys\LowError\SystemErrorHandler::getExceptionInfo($e);
                $errinfo['no_send_error'] = true;
                \DpSys\LowError\SystemErrorHandler::logErrorInfo($errinfo);
            }
        }

        if (!class_exists($cls, false)) {
            if (false === $cache = $this->getCacheFilename($name)) {
                eval('?>'.$this->compileSource($this->loader->getSource($name), $name));
            } else {
                if (strpos($cache, 'dptpl://') === 0) {
                    $tplinfo = \Application\DeskPRO\Twig\Loader\DbStreamWrapper::getTemplateInfo(str_replace('dptpl://load/', '', $cache));
                    eval('?>'.$tplinfo['template_compiled']);
                } else {
                    if (!is_file($cache) || ($this->isAutoReload() && !$this->isTemplateFresh($name, filemtime($cache)))) {
                        $fallback = false;
                        $e        = null;
                        try {
                            $this->writeCacheFile($cache, $this->compileSource($this->loader->getSource($name), $name));
                            require_once $cache;
                        } catch (\Exception $e) {
                            $fallback = true;
                        }

                        if ($fallback) {
                            if (!isset($GLOBALS['DP_NOLOG_TPL_CACHE_ERR']) || !$GLOBALS['DP_NOLOG_TPL_CACHE_ERR']) {
                                // Fallback on just evalling the template so everything
                                $prev = null;
                            }

                            $source = $this->compileSource($this->loader->getSource($name), $name);
                            eval('?>'.$source);
                        }
                    } else {
                        require_once $cache;
                    }
                }
            }
        }

        if (!$this->runtimeInitialized) {
            $this->initRuntime();
        }

        return $this->loadedTemplates[$cls] = new $cls($this);
    }

    /**
     * If theres a custom template with an error, then
     * we'll try and use the default template instead.
     *
     * @param $name
     *
     * @return string
     */
    public function markCustomTemplateAsCrashed($name)
    {
        if ($this->loader->dbHasTemplate($name)) {
            $this->loader->markCustomTemplateAsCrashed($name);
        }
    }

    /**
     * Check if a particular template is a custom template.
     *
     * @param $name
     *
     * @return mixed
     */
    public function isCustomTemplate($name)
    {
        if ($this->loader instanceof HybridLoader && $this->loader->dbHasTemplate($name)) {
            return true;
        }

        return false;
    }

    public function getTemplateClass($name, $index = null)
    {
        $key = $this->getLoader()->getCacheKey($name);
        $key = str_replace(DP_DIR.DIRECTORY_SEPARATOR, '', $key);

        $extensions = array_keys($this->extensions);
        sort($extensions);

        $key .= implode('', $extensions);
        $key .= function_exists('twig_template_get_attributes');

        $class = $this->templateClassPrefix.hash('sha256', $key).(null === $index ? '' : '_'.$index);

        return $class;
    }

    public function getCacheFilename($name)
    {
        if (!($this->loader instanceof HybridLoader) || !$this->loader->dbHasTemplate($name)) {
            $key = $this->cache->generateKey($name, $this->getTemplateClass($name));

            return !$key ? false : $key;
        }

        return 'dptpl://load/'.$name;
    }

    public function isTemplateFresh($name, $time)
    {
        if ($this->loader instanceof HybridLoader && $this->loader->dbHasTemplate($name)) {
            return true;
        }

        return $this->loader->isFresh($name, $time);
    }

    /**
     * @param $template_code
     * @param array $vars
     *
     * @throws \Exception|null
     *
     * @return null|string
     */
    public function renderStringTemplate($template_code, array $vars = [])
    {
        $old_loader = $this->getLoader();
        $old_cache  = $this->getCache();

        $arr_loader = new \Twig_Loader_Array([
            'template' => $template_code,
        ]);

        $this->setLoader($arr_loader);
        $this->setCache(false);

        $result    = null;
        $exception = null;
        try {
            $result = $this->render('template', $vars);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->setLoader($old_loader);
        $this->setCache($old_cache);

        if ($exception) {
            throw $exception;
        }

        return $result;
    }
}
