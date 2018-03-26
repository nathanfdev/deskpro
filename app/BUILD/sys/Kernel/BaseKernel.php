<?php

namespace DpSys\Kernel;

use Application\AgentBundle\AgentBundle;
use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Bundle\ApiBundle\ApiBundle;
use DeskPRO\Bundle\AppBundle\AppBundle;
use DeskPRO\Bundle\PortalBundle\PortalBundle;
use DpRun\DpEnv;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class BaseKernel.
 */
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

        if ($this->container->has('logger')) {
            SystemErrorHandler::setErrorLogger($this->container->get('logger'));
        }

        // Legacy
        App::$container = $this->container;
        if ($this->container->has('deskpro.sys_events_loader')) {
            $this->container->get('deskpro.sys_events_loader');
        }

        if ($this->container->has('monolog.logger.php')) {
            $this->container->get('monolog.logger.php')->info('Kernel Type: '.get_class($this));
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
     * @return ApiBundle|AppBundle|PortalBundle|AgentBundle
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

        $cacheFile = $cache->getPath();
        $content   = file_get_contents($cacheFile);

        // Re-write app dir paths
        $content = str_replace(
            '$this->targetDirs[4].\'/app/BUILD',
            '$this->getDpAppDir().\'',
            $content
        );

        foreach (['tmp', 'cache', 'debug', 'backups', 'files'] as $userDirId) {
            $content = str_replace(
                "'__dp__user_{$userDirId}_dir__'",
                '$this->getDpUserDir(\''.$userDirId.'\')',
                $content
            );
        }

        $parts = preg_split('/\s*private \$parameters;/', $content);
        if (count($parts) !== 2) {
            throw new \Exception("Unable to dump $class $baseClass container");
        }

        $getter = <<<'CODE'
    private $dpBuildId = null;
    private function getDpBuildId()
    {
        if ($this->dpBuildId !== null) {
            return $this->dpBuildId;
        }

        return $this->dpBuildId = basename(realpath(__DIR__.'/../'));
    }
    
    private function getDpAppDir()
    {
        return $this->targetDirs[4].'/app/' . $this->getDpBuildId();
    }

    private function getDpUserDir($type)
    {
        global $DP_ENV;
        switch ($type) {
            case 'tmp':     return $DP_ENV->getUserTmpDir();
            case 'cache':   return $DP_ENV->getUserCacheDir();
            case 'debug':   return $DP_ENV->getUserDebugDir();
            case 'backups': return $DP_ENV->getUserBackupsDir();
            case 'files':   return $DP_ENV->getUserFilesDir();
            default: throw new \InvalidArgumentException();
        }
    }
CODE;

        $content = $parts[0]."\n\n".$getter."\n\n".$parts[1];

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

        // these are inserted statically at compile-time,
        // and we replace them with real values in our dumpContainer above
        $params['dp.app_dir']          = $this->dpEnv->getAppDir();
        $params['dp.user.tmp_dir']     = '__dp__user_tmp_dir__';
        $params['dp.user.cache_dir']   = '__dp__user_cache_dir__';
        $params['dp.user.debug_dir']   = '__dp__user_debug_dir__';
        $params['dp.user.backups_dir'] = '__dp__user_backups_dir__';
        $params['dp.user.files_dir']   = '__dp__user_files_dir__';

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

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        if (false === $this->booted) {
            return;
        }

        if ($this->environment !== 'test') {
            parent::shutdown();
        }

        // In test env clean up all container services from all object references
        // to prevent memory leaks when sharing the kernel between test scenarios
        else {
            $container = $this->container;
            parent::shutdown();
            $this->cleanupContainer($container);
        }
    }

    /**
     * @param Container $container
     */
    private function cleanupContainer(Container $container)
    {
        // close mysql connections
        if ($container->has('doctrine.orm.default_entity_manager')) {
            $container->get('doctrine.orm.default_entity_manager')->getConnection()->close();
        }
        if ($container->has('doctrine.orm.system_entity_manager')) {
            $container->get('doctrine.orm.system_entity_manager')->getConnection()->close();
        }
        if ($container->has('doctrine.orm.audit_entity_manager')) {
            $container->get('doctrine.orm.audit_entity_manager')->getConnection()->close();
        }

        // unset runtime vars
        $reflection = new \ReflectionClass(DpEnv::class);
        $property   = $reflection->getProperty('runtime_vars');
        $property->setAccessible(true);
        $property->setValue($container->get('deskpro.low_dp_env'), []);
        $property->setAccessible(false);

        // remove all container references from all loaded services
        $containerReflection        = new \ReflectionObject($container);
        $servicesPropertyReflection = $containerReflection->getProperty('services');
        $servicesPropertyReflection->setAccessible(true);
        $services = $servicesPropertyReflection->getValue($container) ?: [];
        foreach ($services as $id => $service) {
            if (in_array($id, ['kernel', 'http_kernel', 'deskpro.low_dp_env'])) {
                continue;
            }

            $serviceReflection       = new \ReflectionObject($service);
            $propertiesReflections   = $serviceReflection->getProperties();
            $propertiesDefaultValues = $serviceReflection->getDefaultProperties();

            foreach ($propertiesReflections as $servicePropertyReflection) {
                $defaultPropertyValue = null;
                if (isset($propertiesDefaultValues[$servicePropertyReflection->getName()])) {
                    $defaultPropertyValue = $propertiesDefaultValues[$servicePropertyReflection->getName()];
                }
                $servicePropertyReflection->setAccessible(true);
                $servicePropertyReflection->setValue($service, $defaultPropertyValue);
            }
        }
        $servicesPropertyReflection->setValue($container, []);
    }
}
