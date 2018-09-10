<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig;

use Application\DeskPRO\Twig\Loader\DbStreamWrapper;
use Application\DeskPRO\Twig\Template;
use Application\EmailBundle\Twig\Extension\TemplatingExtension;
use DeskPRO\Bundle\SendmailBundle\Twig\Loader\HybridLoader;
use DpSys\LowError\SystemErrorHandler;

class Environment extends \Twig_Environment
{
    /** @var bool */
    protected $extDirty = false;

    public function __construct(\Twig_LoaderInterface $loader = null, $options = [])
    {
        static $hasDone = false;

        if (defined('DP_DEBUG') && (empty($options['auto_reload']) || $options['auto_reload'] === null)) {
            if (DP_DEBUG) {
                $options['auto_reload'] = true;
            } else {
                $options['auto_reload'] = false;
            }
        }

        if (!$hasDone) {
            if (!in_array('dptpl', stream_get_wrappers())) {
                stream_wrapper_register('dptpl', DbStreamWrapper::class, 0);
            }
            $hasDone = true;
        }

        $options['base_template_class'] = Template::class;

        parent::__construct($loader, $options);
    }

    public function addExtension(\Twig_ExtensionInterface $extension)
    {
        parent::addExtension($extension);
        $this->extDirty = true;
    }

    public function setExtensions(array $extensions)
    {
        parent::setExtensions($extensions);
        $this->extDirty = true;
    }

    public function getExtensions()
    {
        // Ensures the DeskPRO filters and functions are always used over the default
        if ($this->extDirty) {
            $this->extDirty = false;

            $setExt    = [];
            $appendExt = [];

            foreach ($this->extensions as $k => $ext) {
                if ($ext instanceof TemplatingExtension) {
                    $appendExt[$k] = $ext;
                } else {
                    $setExt[$k] = $ext;
                }
            }
            foreach ($appendExt as $k => $ext) {
                $setExt[$k] = $ext;
            }

            $this->extensions = $setExt;
        }

        return $this->extensions;
    }

    public function loadTemplate($name, $index = null)
    {
        $nameStr = (string) $name;
        if (!$this->isCustomTemplate($nameStr)) {
            return $this->doLoadTemplate($name, $index);
        } else {
            try {
                return $this->doLoadTemplate($name, $index);
            } catch (\Exception $e) {
                $errinfo                  = SystemErrorHandler::getExceptionInfo($e);
                $errinfo['no_send_error'] = true;
                SystemErrorHandler::logErrorInfo($errinfo);

                $this->markCustomTemplateAsCrashed($nameStr);

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

        if (!class_exists($cls, false)) {
            if (false === $cache = $this->getCacheFilename($name)) {
                eval('?>'.$this->compileSource($this->loader->getSourceContext($name), $name));
            } else {
                if (strpos($cache, 'dptpl://') === 0) {
                    $tplinfo = DbStreamWrapper::getTemplateInfo(str_replace('dptpl://load/', '', $cache));
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
                                if ($e) {
                                    $prev = $e;
                                }

                                $nameStr = (string) $name;
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
     * @param $templateCode
     * @param array $vars
     *
     * @throws \Exception|null
     *
     * @return null|string
     */
    public function renderStringTemplate($templateCode, array $vars = [])
    {
        $oldLoader = $this->getLoader();
        $oldCache  = $this->getCache();

        $arrLoader = new \Twig_Loader_Array([
            'template' => $templateCode,
        ]);

        $this->setLoader($arrLoader);
        $this->setCache(false);

        $result    = null;
        $exception = null;
        try {
            $result = $this->render('template', $vars);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->setLoader($oldLoader);
        $this->setCache($oldCache);

        if ($exception) {
            throw $exception;
        }

        return $result;
    }
}
