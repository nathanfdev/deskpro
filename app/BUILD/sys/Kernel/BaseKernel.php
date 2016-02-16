<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DpSys\Kernel;

use Application\AgentBundle\AgentBundle;
use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Bundle\ApiBundle\ApiBundle;
use DeskPRO\Bundle\AppBundle\AppBundle;
use DeskPRO\Bundle\PortalBundle\PortalBundle;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\HttpKernel\Kernel;

abstract class BaseKernel extends Kernel
{
    /**
     * @var \DpRun\DpEnv
     */
    private $dpEnv;

    /**
     * @var array
     */
    private $instantied_but_not_used_bundles = [];

    /**
     * BaseKernel constructor.
     *
     * @param string            $environment
     * @param bool              $debug
     * @param \DpRun\DpEnv|null $env
     */
    public function __construct($environment, $debug, \DpRun\DpEnv $env = null)
    {
        if ($env === null) {
            if ($GLOBALS['DP_ENV']) {
                $env = $GLOBALS['DP_ENV'];
            }
        }
        $this->dpEnv = $env;
        parent::__construct($environment, $debug);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if (null === $this->name) {
            $parts      = explode('\\', get_class($this));
            $name       = array_pop($parts);
            $name       = str_replace('Kernel', '', $name);
            $this->name = $name;
        }

        return $this->name;
    }

    /**
     * @return \DpRun\DpEnv
     */
    public function getDpEnv()
    {
        return $this->dpEnv;
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        parent::boot();

        if ($this->container instanceof DeskproContainer) {
            $this->container->kernel = $this;
        }

        // Legacy
        App::$container = $this->container;
        if ($this->container->has('deskpro.sys_events_loader')) {
            $this->container->get('deskpro.sys_events_loader');
        }
    }

    /**
     * We extend the base functionality of getBundle to allow kernels that don't use a bundle to
     * still reference that bundle in config (reference like "@PortalBundle/Resources/config/routing.yml") etc.
     *
     * For the 99% case, we are never going to call this with a bundle that is not
     * being used in the kernel anyway, usually only for generating the container.
     * If a bundle ver does get called here outside of that, consider registering the
     * bundle into the kernel anyway.
     */
    public function getBundle($name, $first = true)
    {
        if (!isset($this->bundleMap[$name])) {

            /*
             * USEFUL FOR CONFIG/NAMESPACES ONLY when a kernel doesn't have the bundle but needs
             * to locate a resource on that other bundle.
             *
             * We have multiple kernels, and sometimes we need the PATH to another bundle
             * for routing and config purposes.  We do NOT want all bundles in every kernel,
             * but if the ApiKernel encounters @PortalBundle/config/routing.yml and we are in the
             * ApiKernel that does not have PortalBundle enabled, it will throw an error.
             *
             * To avoid this, we instante and return the bundle class so it can access the proper
             * path and namespaces for that bundle instead of throwing an error, while at the
             * same time we retain the original Symfony Kernel way of locating resources.
             *
             * It is important that you do NOT add this new bundle to the $this->bundleMap.
             */

            // we are very explicit of what bundles are allowed here
            // the kernels that actually use these bundles won't hit here because
            // that bundle would already be in the $this->bundleMap in the if statement above
            switch ($name) {
                case 'PortalBundle':
                    // ApiKernel does not have PortalBundle
                    // DpKernel does not have PortalBundle
                    return [$this->getUnusedBundle('PortalBundle')];
                case 'ApiBundle':
                    // PortalKernel does not have ApiBundle
                    // DpKernel does not have ApiBundle
                    return [$this->getUnusedBundle('ApiBundle')];
                case 'AppBundle':
                    // DpKernel does not have AppBundle
                    return [$this->getUnusedBundle('AppBundle')];
                case 'AgentBundle':
                    // PortalKernel does not have AgentBundle
                    // ApiKernel does not have AgentBundle
                    return [$this->getUnusedBundle('AgentBundle')];
                default:
                    break;
            }
        }

        return parent::getBundle($name, $first);
    }

    /**
     * This just makes sure we only instante each bundle once, for a very very small performance gain.
     *
     * @param $name
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getUnusedBundle($name)
    {
        if (!isset($this->instantied_but_not_used_bundles[$name])) {
            $this->instantied_but_not_used_bundles[$name] = $this->instantiateBundle($name);
        }

        return $this->instantied_but_not_used_bundles[$name];
    }

    /**
     * A factory for bundle instances based on name.
     *
     * @param $name
     *
     * @throws \Exception
     *
     * @return ApiBundle|AppBundle|PortalBundle
     */
    private function instantiateBundle($name)
    {
        switch ($name) {
            case 'PortalBundle':
                return new PortalBundle();
            case 'ApiBundle':
                return new ApiBundle();
            case 'AppBundle':
                return new AppBundle();
            case 'AgentBundle':
                return new AgentBundle();
        }

        throw new \Exception('oops, that bundle cannot be auto-instantiated by DpSys\Kernel\BaseKernel');
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        // entity loader required to construct symfony container
        // so enable it temporarily while the container builds
        $v = libxml_disable_entity_loader(false);

        $GLOBALS['DP_CONTAINER_IS_BUILDING'] = true;
        parent::initializeContainer();
        unset($GLOBALS['DP_CONTAINER_IS_BUILDING']);

        libxml_disable_entity_loader($v);
    }

    /**
     * {@inheritdoc}
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        // Clear the dql cache when the container is regenerated as well
        $dql_cache = $this->getCacheDir().DIRECTORY_SEPARATOR.'dql.cache';
        if (file_exists($dql_cache)) {
            @unlink($dql_cache);
        }

        parent::dumpContainer($cache, $container, $class, $baseClass);

        $cacheFile = (string) $cache;
        $content   = file_get_contents($cacheFile);

        // Re-write absolute paths to use DP_ROOT instead
        $content = str_replace("'".DP_APP_DIR, 'DP_APP_DIR.\'', $content);
        // Correct double slash paths
        // Empty logs dir that isn't used (we get it from conf)
        $content = preg_replace("#'kernel\\.logs_dir' => '(.*?)'#", "'kernel.logs_dir' => '".$this->getLogDir()."'", $content);

        $cache->write($content, $container->getResources());
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerBaseClass()
    {
        return '\\Application\\DeskPRO\\DependencyInjection\\DeskproContainer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters()
    {
        $params            = parent::getKernelParameters();
        $params['DP_ROOT'] = DP_ROOT; //legacy

        $params['dp.app_dir']          = $this->dpEnv->getAppDir();
        $params['dp.user.tmp_dir']     = $this->dpEnv->getUserTmpDir();
        $params['dp.user.cache_dir']   = $this->dpEnv->getUserCacheDir();
        $params['dp.user.debug_dir']   = $this->dpEnv->getUserDebugDir();
        $params['dp.user.backups_dir'] = $this->dpEnv->getUserBackupsDir();
        $params['dp.user.files_dir']   = $this->dpEnv->getUserFilesDir();

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir()
    {
        return DP_APP_DIR.'/sys';
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return $this->dpEnv->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$this->getEnvironment();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return $this->dpEnv->getUserLogsDir();
    }
}
