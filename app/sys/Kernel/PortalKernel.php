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
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class PortalKernel extends BaseKernel
{
    public function getName()
    {
        return 'portal';
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return BundleInterface[] An array of bundle instances.
     *
     * @api
     */
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new \WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new \FOS\HttpCacheBundle\FOSHttpCacheBundle(),
            new \FOS\ElasticaBundle\FOSElasticaBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \Nelmio\CorsBundle\NelmioCorsBundle(),
            new \Gregwar\CaptchaBundle\GregwarCaptchaBundle(),

            new \Application\DeskPRO\DeskPROBundle(),
            new \Application\EmailBundle\EmailBundle(),
            new \Application\AgentBundle\AgentBundle(),

            new \DeskPRO\Bundle\AppBundle\AppBundle(),
            new \DeskPRO\Bundle\PortalBundle\PortalBundle(),
        );

        if ('dev' === $this->getEnvironment()
            or
            'test' === $this->getEnvironment()) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
        }

        if ('test' === $this->getEnvironment()) {
            $bundles[] = new \DpTestSrc\TestBundle\TestBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerBaseClass()
    {
        return '\\Application\\DeskPRO\\DependencyInjection\\DeskproContainer';
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @api
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(DP_ROOT.'/sys/config/portal/portal_config_'.$this->getEnvironment().'.yml');
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
     * @return string
     */
    public function getRootDir()
    {
        return DP_ROOT.'/sys';
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        static $cache_dir = null;

        if ($cache_dir === null) {
            if (defined('DPC_IS_CLOUD')) {
                $cache_dir = dp_get_cache_dir().'/portal/'.$this->environment.'-cloud';
            } else {
                $cache_dir = dp_get_cache_dir().'/portal/'.$this->environment.'';
            }
        }

        return $cache_dir;
    }

    private function prepareCachePaths()
    {
        // Make sure the cache dirs exist
        if (!is_dir($this->getCacheDir())) {
            mkdir($this->getCacheDir(), 0777, true);
        }

        $env_dir = realpath($this->getCacheDir().'/../..');

        if (!file_exists($env_dir.'/doctrine-proxies')) {
            mkdir($env_dir.'/doctrine-proxies', 0777, true);
        }
        if (!file_exists($env_dir.'/twig-compiled')) {
            mkdir($env_dir.'/twig-compiled', 0777, true);
        }

        @chmod($this->getCacheDir(), 0777);
        @chmod($env_dir.'/doctrine-proxies', 0777);
        @chmod($env_dir.'/twig-compiled', 0777);
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        $this->prepareCachePaths();

        // entity loader required to construct symfony container
        // so enable it temporarily while the container builds
        $v = libxml_disable_entity_loader(false);

        parent::initializeContainer();

        // TODO: this is the major pain point for us where the App:: globals enter the kernel space
        // I didn't need this until Auth, because the Person entity itself gets objects that are necessary
        App::$container = $this->getContainer();

        libxml_disable_entity_loader($v);
    }

    /**
     * {@inheritdoc}
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        $this->prepareCachePaths();

        // Clear the dql cache when the container is regenerated as well
        $dql_cache = dp_get_tmp_dir().DIRECTORY_SEPARATOR.'dql.cache';
        if (file_exists($dql_cache)) {
            @unlink($dql_cache);
        }

        parent::dumpContainer($cache, $container, $class, $baseClass);

        $cacheFile = (string) $cache;
        $content   = file_get_contents($cacheFile);

        // Re-write absolute paths to use DP_ROOT instead
        $content = str_replace("'".DP_ROOT, 'DP_ROOT.\'', $content);
        // Correct double slash paths
        $content = str_replace('prod//', 'prod/', $content);
        // Empty logs dir that isn't used (we get it from conf)
        $content = preg_replace("#'kernel\\.logs_dir' => '(.*?)'#", "'kernel.logs_dir' => ''", $content);

        $cache->write($content, $container->getResources());
    }
}
