<?php

namespace DpSys\CodePlugin;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;
use DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder\AbstractPlaceholder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class CodePluginManager
{
    /**
     * @var CodePlugins
     */
    private static $inst;

    /**
     * @var CodePlugin[]
     */
    private $plugins = [];

    /**
     * @return self
     */
    public static function getManager()
    {
        if (!self::$inst) {
            self::$inst = new self();
        }

        return self::$inst;
    }

    /**
     * @param CodePlugin $plugin
     */
    public function add(CodePlugin $plugin)
    {
        $this->plugins[] = $plugin;
    }

    /**
     * @param string $classname
     *
     * @return string
     */
    public function getRewrittenClassName($classname)
    {
        foreach ($this->plugins as $plugin) {
            $classname = $plugin->rewriteClassName($classname);
        }

        return $classname;
    }

    /**
     * @return array
     */
    public function getExtraDefaultDataClasses()
    {
        $extra = [];

        foreach ($this->plugins as $plugin) {
            $extra = array_merge($extra, $plugin->getDefaultDataClasses());
        }

        return $extra;
    }

    /**
     * @param string $file
     *
     * @return array
     */
    public function loadExtraLangFile($file)
    {
        $extra = [];

        foreach ($this->plugins as $plugin) {
            $extra = array_merge($extra, $plugin->loadLanguageFile($file));
        }

        return $extra;
    }

    /**
     * @param string $file
     *
     * @return null|string
     */
    public function getServeAssetFilePath($file)
    {
        foreach ($this->plugins as $plugin) {
            $f = $plugin->getServeAssetPath($file);
            if ($f) {
                return $f;
            }
        }

        return null;
    }

    /**
     * @param string    $id
     * @param Container $container
     *
     * @return string
     */
    public function getCustomHtml($id, Container $container)
    {
        $html = [];
        foreach ($this->plugins as $plugin) {
            $h = $plugin->getCustomHtml($id, $container);
            if ($h) {
                $html[] = $h;
            }
        }

        return implode("\n", $html);
    }

    /**
     * @param array  $fixtures
     * @param string $installSource
     *
     * @return array
     */
    public function filterInstallFixtures(array $fixtures, $installSource)
    {
        foreach ($this->plugins as $plugin) {
            $fixtures = $plugin->filterInstallFixtures($fixtures, $installSource);
        }

        return $fixtures;
    }

    /**
     * @param string    $id
     * @param Container $container
     *
     * @return Response|null
     */
    public function handleGoRequest($id, Container $container)
    {
        foreach ($this->plugins as $plugin) {
            if ($res = $plugin->handleGoRequest($id, $container)) {
                return $res;
            }
        }
    }

    /**
     * @param Request $request
     * @param string  $context    agent or user
     * @param string  $controller
     * @param stirng  $action
     *
     * @return null|string The name of the class and action. e.g. Foo\Bar::myAction
     */
    public function routeScriptController(Request $request, $context, $controller, $action)
    {
        foreach ($this->plugins as $plugin) {
            if ($res = $plugin->routeScriptController($request, $context, $controller, $action)) {
                return $res;
            }
        }

        return null;
    }

    /**
     * @param string             $placeholderName
     * @param DpqlContextStorage $contextStorage
     * @param Container          $container
     *
     * @return AbstractPlaceholder|null
     */
    public function getDpqlPlaceholder($placeholderName, DpqlContextStorage $contextStorage, Container $container)
    {
        foreach ($this->plugins as $plugin) {
            if ($p = $plugin->getDpqlPlaceholder($placeholderName, $contextStorage, $container)) {
                return $p;
            }
        }

        return null;
    }
}
