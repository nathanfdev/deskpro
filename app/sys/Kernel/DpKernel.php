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
namespace DeskPRO\Kernel;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class DpKernel extends AbstractKernel
{
    /**
     * @var bool
     */
    private $has_booted = false;

    /**
     * @var string
     */
    private $interface;

    /**
     * @var DeskproContainer
     */
    protected $container;

    /**
     * @param string $environment
     * @param bool   $debug
     * @param string $interface
     */
    public function __construct($environment, $debug, $interface = 'unknown')
    {
        parent::__construct($environment, $debug);

        $this->name      = $this->getName();
        $this->interface = $interface;

        if (!defined('DP_DEBUG')) {
            if ($this->isDebug()) {
                define('DP_DEBUG', true);
            } else {
                define('DP_DEBUG', false);
            }
        }

        set_error_handler('DeskPRO\\Kernel\\KernelErrorHandler::handleError', E_ALL | E_STRICT);
        set_exception_handler('DeskPRO\\Kernel\\KernelErrorHandler::handleException');
        register_shutdown_function('DeskPRO\\Kernel\\KernelErrorHandler::shutdownCheckFatalError');
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->has_booted) {
            return;
        }
        $this->has_booted = true;

        parent::boot();

        $this->container->kernel = $this;
        App::$container          = $this->container;

        if ($this->container->has('deskpro.sys_events_loader')) {
            $this->container->get('deskpro.sys_events_loader');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        if ($this->environment == 'dev') {
            $routing_cache_cleaner = new \Application\DeskPRO\Routing\CacheCleaner();
            if (!$routing_cache_cleaner->isFresh()) {
                $routing_cache_cleaner->clearCache();
            }
        }

        if ($this->environment == 'prod' && !defined('DP_BUILDING') && !defined('DPC_IS_CLOUD')) {
            // If the container doesnt exist and we're in prod, then means we're installing an update.
            // Halt now. This prevents the system from trying to generate the cache itself,
            // even though the new files will be installed in a second.
            $cache_file = $this->getCacheDir().'/'.$this->getContainerClass().'.php';
            if (!is_file($cache_file)) {
                echo HelpdeskOfflineMessage::getOfflinePage('Currently installing updates');
                exit;
            }
        }

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
        // Make sure the cache dirs exist
        $env_dir = realpath($this->getCacheDir().'/../');
        if (!is_dir($this->getCacheDir())) {
            mkdir($this->getCacheDir(), 0777, true);
        }
        if (!file_exists($env_dir.'/doctrine-proxies')) {
            mkdir($env_dir.'/doctrine-proxies', 0777, true);
        }
        if (!file_exists($env_dir.'/twig-compiled')) {
            @mkdir($env_dir.'/twig-compiled', 0777, true);
        }

        @chmod($this->getCacheDir(), 0777);
        @chmod($env_dir.'/doctrine-proxies', 0777);
        @chmod($env_dir.'/twig-compiled', 0777);

        // Clear the dql cache when the container is regenerated as well
        $dql_cache = dp_get_tmp_dir().DIRECTORY_SEPARATOR.'dql.cache';
        if (file_exists($dql_cache)) {
            @unlink($dql_cache);
        }

        // cache the container
        $dumper  = new PhpDumper($container);
        $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));
        if (!$this->debug) {
            $content = self::stripComments($content);
        }

        // Re-write absolute paths to use DP_ROOT instead
        $content = str_replace("'".DP_ROOT, 'DP_ROOT.\'', $content);
        // Correct double slash paths
        $content = str_replace('prod//', 'prod/', $content);
        $content = str_replace('dev//', 'dev/', $content);
        // Empty logs dir that isn't used (we get it from conf)
        $content = preg_replace("#'kernel\\.logs_dir' => '(.*?)'#", "'kernel.logs_dir' => ''", $content);

        $cache->write($content, $container->getResources());
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerClass()
    {
        $parts    = explode('\\', get_class($this));
        $basename = array_pop($parts);

        $container_name = $basename;
        if ($this->environment != 'prod') {
            $container_name .= ucfirst($this->environment);
        }
        if ($this->debug) {
            $container_name .= 'Debug';
        }
        $container_name .= 'Container';

        return $container_name;
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return DP_ROOT.'/sys';
    }

    public function getName()
    {
        return 'agent';
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        static $cache_dir = null;

        if ($cache_dir === null) {
            if (defined('DPC_IS_CLOUD')) {
                $cache_dir = dp_get_cache_dir().'/'.$this->environment.'-cloud';
            } else {
                $cache_dir = dp_get_cache_dir().'/'.$this->environment;
            }
        }

        return $cache_dir;
    }

    /**
     * @deprecated Use dp_get_log_dir()
     *
     * @return string
     */
    public function getLogDir()
    {
        if (!function_exists('dp_get_log_dir')) {
            require_once DP_ROOT.'/sys/load_config.php';
        }

        return dp_get_log_dir();
    }

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters()
    {
        $params            = parent::getKernelParameters();
        $params['DP_ROOT'] = DP_ROOT;

        return $params;
    }

    /**
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
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
    public function registerBundleDirs()
    {
        $bundle_dirs = array(
            'Application' => DP_ROOT.'/src/Application',
            'Bundle'      => DP_ROOT.'/src/Bundle',
        );

        if (defined('DPC_IS_CLOUD')) {
            $bundle_dirs['Cloud'] = DP_ROOT.'/src/Cloud';
        }

        return $bundle_dirs;
    }

    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(DP_ROOT.'/sys/config/config_'.$this->getEnvironment().'.php');
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassCache($name = 'classes', $extension = '.php')
    {
        // Nothing, we handle the class cache as part of the build and include it in KernelBooter
    }

    /**
     * {@inheritdoc}
     */
    public function setClassCache(array $classes)
    {
        if (defined('DP_BUILDING')) {
            parent::setClassCache($classes);
        }
    }

    ####################################################################################################################

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \FOS\ElasticaBundle\FOSElasticaBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new \WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),

            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),

            new \Application\DeskPRO\DeskPROBundle(),
            new \Application\EmailBundle\EmailBundle(),
            new \Application\AdminInterfaceBundle\AdminInterfaceBundle(),
            new \Application\AgentBundle\AgentBundle(),
            new \Application\ReportsInterfaceBundle\ReportsInterfaceBundle(),
            new \Application\UserBundle\UserBundle(),
            new \Application\LegacyApiBundle\LegacyApiBundle(),
            new \Application\ImportBundle\ImportBundle(),

            new \DeskPRO\Bundle\AppBundle\AppBundle(),
        );

        if (defined('DPC_IS_CLOUD')) {
            $bundles = array_merge($bundles, array(
                new \Cloud\LegacyApiBundle\CloudLegacyApiBundle(),
                new \Cloud\AdminInterfaceBundle\CloudAdminInterfaceBundle(),
            ));
        }

        return $bundles;
    }
}
