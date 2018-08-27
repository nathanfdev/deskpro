<?php

/**
 * DeskPRO.
 */

namespace DpSys\Kernel;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Symfony\Component\Config\Loader\LoaderInterface;

class DpKernel extends BaseKernel
{
    /**
     * @var string
     */
    private $interface;

    /**
     * @var DeskproContainer
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param string       $environment
     * @param bool         $debug
     * @param \DpRun\DpEnv $env
     */
    public function __construct($environment, $debug, \DpRun\DpEnv $env = null)
    {
        parent::__construct($environment, $debug, $env);

        $this->interface = DP_INTERFACE;

        if (!defined('DP_DEBUG')) {
            if ($this->isDebug()) {
                define('DP_DEBUG', true);
            } else {
                define('DP_DEBUG', false);
            }
        }
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
    public function registerBundleDirs()
    {
        $bundle_dirs = [
            'Application' => DP_ROOT.'/src/Application',
            'Bundle'      => DP_ROOT.'/src/Bundle',
        ];

        if (defined('DPC_IS_CLOUD')) {
            $bundle_dirs['Cloud'] = DP_ROOT.'/src/Cloud';
        }

        return $bundle_dirs;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(DP_ROOT.'/sys/config/config_'.$this->getEnvironment().'.php');
    }

    //###################################################################################################################

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new \WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \FOS\ElasticaBundle\FOSElasticaBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),

            new \Gregwar\CaptchaBundle\GregwarCaptchaBundle(),
            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),

            new \Application\DeskPRO\DeskPROBundle(),
            new \Application\EmailBundle\EmailBundle(),
            new \DeskPRO\Bundle\SendmailBundle\SendmailBundle(),
            new \DeskPRO\Bundle\AppBundle\AppBundle(),
            new \DeskPRO\Bundle\ReportBundle\ReportBundle(),
            new \DeskPRO\Bundle\AppStoreBundle\AppStoreBundle(),
            new \DeskPRO\Bundle\SystemBundle\SystemBundle(),
            new \DeskPRO\Bundle\AuditBundle\AuditBundle(),
            new \DeskPRO\Bundle\ImportBundle\ImportBundle(),
            new \DeskPRO\Bundle\VoiceBundle\VoiceBundle(),

            new \Application\AdminInterfaceBundle\AdminInterfaceBundle(),
            new \Application\AgentBundle\AgentBundle(),
            new \Application\ReportsInterfaceBundle\ReportsInterfaceBundle(),
            new \Application\UserBundle\UserBundle(),
            new \Application\LegacyApiBundle\LegacyApiBundle(),
            new \DeskPRO\Bundle\UpdateBundle\UpdateBundle(),
        ];

        if (defined('DPC_IS_CLOUD')) {
            $bundles = array_merge($bundles, [
                new \Cloud\LegacyApiBundle\CloudLegacyApiBundle(),
                new \Cloud\AdminInterfaceBundle\CloudAdminInterfaceBundle(),
                new \DeskPROCloud\Bundle\CloudBillingBundle\CloudBillingBundle(),
            ]);
        }

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }
}
