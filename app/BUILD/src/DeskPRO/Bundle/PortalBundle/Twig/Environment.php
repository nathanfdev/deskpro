<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\App;
use DeskPRO\Bundle\PortalBundle\Twig\Exception\CustomTemplateCompilationException;
use DeskPRO\Bundle\PortalBundle\Twig\Exception\CustomTemplateNotFoundException;
use DeskPRO\Bundle\PortalBundle\Twig\Exception\PortalLoaderException;

/**
 * Class Environment.
 */
class Environment extends \Twig_Environment
{
    /**
     * Environment constructor.
     *
     * @param \Twig_LoaderInterface|null $loader
     * @param array                      $options
     */
    public function __construct(\Twig_LoaderInterface $loader = null, $options = [])
    {
        parent::__construct($loader, $options);
    }

    /**
     * @param string $name
     * @param int    $time
     *
     * @return bool
     */
    public function isTemplateFresh($name, $time)
    {
        return $this->getLoader()->isFresh($name, $time);
    }

    public function getTemplateClass($name, $index = null)
    {
        $key = $this->getLoader()->getCacheKey($name);
        $key .= json_encode(array_keys($this->extensions));
        $key .= function_exists('twig_template_get_attributes');

        return $this->templateClassPrefix.hash('sha256', $key).(null === $index ? '' : '_'.$index);
    }

    /**
     * {@inheritdoc}
     */
    public function loadTemplate($name, $index = null)
    {
        $nameStr = (string) $name;
        try {
            return $this->loadTemplateFromDb($nameStr, $index);
        } catch (CustomTemplateCompilationException $e) {
            // should mark it as crashed
            // falling back to render default template
            $this->markCustomTemplateAsCrashed($name);
        } catch (CustomTemplateNotFoundException $e) {
            // falling back to render default template
            // we wont mark it as crashed template, since we are not even found it
        } catch (PortalLoaderException $e) {
            // fallback to simple loading from filesystem with parent class, since we don't have PortalLoader and know
            // nothing about theming
            return parent::loadTemplate($name, $index);
        } finally {
            // should be initialized if not yet initialized any way
            if (!$this->runtimeInitialized) {
                $this->initRuntime();
            }
        }

        return $this->doLoadTemplate($name, $index);
    }

    /**
     * @param $name
     * @param $index
     *
     * @return mixed
     */
    private function loadTemplateFromDb($name, $index)
    {
        try {
            $template = $this->getPortalLoader()->getDbTemplate($name);
            if ($template) {
                $className = $this->getTemplateClass($name, $index);
                if (!class_exists($className, false)) {
                    // we have to check for class existence otherwise whole script will fail without ability to fallback
                    eval('?>'.$this->compileSource($template->getTemplateCode(), $name));
                }
            } else {
                throw new CustomTemplateNotFoundException(sprintf('Template [ %s ] was not found', $name));
            }
        } catch (\Twig_Error $e) {
            throw new CustomTemplateCompilationException(sprintf('Couldn\'t compile custom template [ %s ]', $name), $e);
        }

        return $this->loadedTemplates[$className] = new $className($this);
    }

    /**
     * @param      $name
     * @param null $index
     *
     * @return mixed
     */
    private function doLoadTemplate($name, $index = null)
    {
        $cls = $this->getTemplateClass($name, $index);

        if (isset($this->loadedTemplates[$cls])) {
            return $this->loadedTemplates[$cls];
        }

        if (!class_exists($cls, false)) {
            $this->loadClass($name);
        }

        return $this->loadedTemplates[$cls] = new $cls($this);
    }

    /**
     * @param $name
     *
     * @throws \Twig_Error
     * @throws \Twig_Error_Syntax
     */
    private function loadClass($name)
    {
        if (false === $cache = $this->getCachePath($name)) {
            eval('?>'.$this->compileSource($this->loader->getSource($name), $name));
        } else {
            if (!is_file($cache) || ($this->isAutoReload() && !$this->isTemplateFresh($name, filemtime($cache)))) {
                try {
                    $this->writeCacheFile($cache, $this->compileSource($this->loader->getSource($name), $name));
                    require_once $cache;
                } catch (\Exception $e) {
                    $this->fallback($name, $e);
                }
            } else {
                require_once $cache;
            }
        }
    }

    private function fallback($name, \Exception $previous)
    {
        $source = $this->compileSource($this->loader->getSource($name), $name);
        eval('?>'.$source);
    }

    /**
     * If theres a custom template with an error, then
     * we'll try and use the default template instead.
     *
     * @param $name
     *
     * @return string
     */
    private function markCustomTemplateAsCrashed($name)
    {
        try {
            $portalLoader = $this->getPortalLoader();
            if ($portalLoader->getDbTemplate($name)) {
                $portalLoader->markCustomTemplateAsCrashed($name);
            }
        } catch (PortalLoaderException $e) {
        }
    }

    /**
     * @return PortalLoader
     */
    private function getPortalLoader()
    {
        if (App::$container && App::$container->has('portal_loader.twig')) {
            return App::$container->get('portal_loader.twig');
        }

        throw new PortalLoaderException('Couldn\'t load PortalLoader');
    }

    /**
     * @param $name
     *
     * @return bool
     */
    private function getCachePath($name)
    {
        $key = $this->cache->generateKey($name, $this->getTemplateClass($name));

        return !$key ? false : $key;
    }
}
