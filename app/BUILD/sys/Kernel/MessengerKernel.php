<?php

namespace DpSys\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class MessengerKernel.
 */
class MessengerKernel extends BaseKernel
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
            new \FOS\RestBundle\FOSRestBundle(),
            new \FOS\ElasticaBundle\FOSElasticaBundle(),
            new \FOS\HttpCacheBundle\FOSHttpCacheBundle(),
            new \Nelmio\CorsBundle\NelmioCorsBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),

            new \Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new \Gregwar\CaptchaBundle\GregwarCaptchaBundle(),

            new \DeskPRO\Bundle\AppBundle\AppBundle(),
            new \DeskPRO\Bundle\BrandBundle\BrandBundle(),
            new \DeskPRO\Bundle\SystemBundle\SystemBundle(),
            new \Application\DeskPRO\DeskPROBundle(),
            new \Application\EmailBundle\EmailBundle(),
            new \DeskPRO\Bundle\SendmailBundle\SendmailBundle(),
            new \DeskPRO\Bundle\VoiceBundle\VoiceBundle(),
            new \DeskPRO\Bundle\MessengerBundle\MessengerBundle(),
        ];

        if ('dev' === $this->getEnvironment()
            or
            'test' === $this->getEnvironment()) {
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
        $loader->load(DP_ROOT.'/sys/config/messenger/messenger_config_'.$this->getEnvironment().'.yml');
    }
}
