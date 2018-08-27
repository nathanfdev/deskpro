<?php

namespace DpSys\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class PortalKernel.
 */
class PortalKernel extends BaseKernel
{
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
            new \FOS\HttpCacheBundle\FOSHttpCacheBundle(),
            new \FOS\ElasticaBundle\FOSElasticaBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \Nelmio\CorsBundle\NelmioCorsBundle(),
            new \Gregwar\CaptchaBundle\GregwarCaptchaBundle(),

            new \Application\DeskPRO\DeskPROBundle(),
            new \Application\EmailBundle\EmailBundle(),
            new \DeskPRO\Bundle\SendmailBundle\SendmailBundle(),
            new \Application\AgentBundle\AgentBundle(),

            new \DeskPRO\Bundle\AppBundle\AppBundle(),
            new \DeskPRO\Bundle\PortalBundle\PortalBundle(),
            new \DeskPRO\Bundle\SystemBundle\SystemBundle(),
            new \DeskPRO\Bundle\AuditBundle\AuditBundle(),
            new \DeskPRO\Bundle\AppStoreBundle\AppStoreBundle(),
            new \DeskPRO\Bundle\ReportBundle\ReportBundle(),
            new \DeskPRO\Bundle\VoiceBundle\VoiceBundle(),
        ];

        if ('dev' === $this->getEnvironment()
            or
            'test' === $this->getEnvironment()) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        if ('test' === $this->getEnvironment()) {
            $bundles[] = new \DpTestSrc\TestBundle\TestBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(DP_ROOT.'/sys/config/portal/portal_config_'.$this->getEnvironment().'.yml');
    }
}
