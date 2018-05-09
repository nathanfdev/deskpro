<?php

namespace DpSys\CodePlugin;

use Application\InstallBundle\Data\DefaultDataProcessor;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;
use DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder\AbstractPlaceholder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class CodePlugin
{
    const CUSTOM_HTML_ADMIN_RES     = 'admin_res';
    const CUSTOM_HTML_ADMIN_PRE_RES = 'admin_pre_res';
    const CUSTOM_HTML_AGENT_RES     = 'agent_res';
    const CUSTOM_HTML_AGENT_PRE_RES = 'agent_res_pre';
    const CUSTOM_HTML_AGENT_PRINT   = 'agent_print';
    const CUSTOM_HTML_REPOTS_RES    = 'reports_res';

    /**
     * @return string
     */
    public function getPluginBasePath()
    {
        return realpath(__FILE__);
    }

    /**
     * In specific cases where the system is looking up classnames
     * plugins can re-write the class name here to change the behaviour.
     *
     * Usually you'd modify the class name by overwriting the symfony DI container config,
     * but some parts of Deskpro don't use that.
     *
     * For example, DefaultDataProcessor will use this when creating data classes.
     * You could use this to overwrite a default data class to change it, or turn
     * it into a no-op.
     *
     * @param string $classname
     *
     * @return string
     */
    public function rewriteClassName($classname)
    {
        return $classname;
    }

    /**
     * Get an array of additional default data classes (classes must all extend AbstractDefaultData).
     *
     * @param DefaultDataProcessor $processor
     *
     * @return array
     */
    public function getDefaultDataClasses(DefaultDataProcessor $processor)
    {
        return [];
    }

    /**
     * Gives opportunity to overwrite files at the file loading stage (i.e. language SystemLoader).
     *
     * @param string $file The relative lang file being loaded. e.g. default/agent.php
     *
     * @return array
     */
    public function loadLanguageFile($file)
    {
        return [];
    }

    /**
     * Given a file path requested through file.php/dp-asset/{{file}}, return the path to the file to serve.
     * This can be used to serve assets that live outside of the default Deskpro tree.
     *
     * @param string $file
     *
     * @return string|null
     */
    public function getServeAssetPath($file)
    {
        return null;
    }

    /**
     * @param string    $id        The locaiton ID
     * @param Container $container
     *
     * @return null|string
     */
    public function getCustomHtml($id, Container $container)
    {
        return null;
    }

    /**
     * Lets you filter install fixtures, or add new ones. Note if you add new ones, the default
     * order for fixtures is within 0-500. If you want your fixtures to run last,
     * best start with an order at 1000.
     *
     * @param array  $fixtures
     * @param string $installSource e.g. InstallSession::SOURCE_DEV or InstallSession::SOURCE_BUILDSERVER
     *
     * @return array
     */
    public function filterInstallFixtures(array $fixtures, $installSource)
    {
        return $fixtures;
    }

    /**
     * @param string    $id
     * @param Container $container
     *
     * @return null|Response
     */
    public function handleGoRequest($id, Container $container)
    {
        return null;
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
        return null;
    }

    /**
     * Get extra commands to register on the app bundle.
     *
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getAppCommands()
    {
        return [];
    }

    /**
     * @param strig $contextId
     * @param array $contextOptions
     *
     * @return null|array
     */
    public function getOptionsArray($contextId, array $contextOptions = [])
    {
        return null;
    }
}
