<?php

namespace DpSys\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class DevKernel.
 */
class DevKernel extends BaseKernel
{
    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(DP_ROOT.'/sys/config/dev/dev_config_'.$this->getEnvironment().'.yml');
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
            new \FOS\ElasticaBundle\FOSElasticaBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new \Gregwar\CaptchaBundle\GregwarCaptchaBundle(),

            new \Application\DeskPRO\DeskPROBundle(),
            new \Application\EmailBundle\EmailBundle(),
            new \DeskPRO\Bundle\SendmailBundle\SendmailBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \Nelmio\ApiDocBundle\NelmioApiDocBundle(),

            new \DeskPRO\Bundle\AppBundle\AppBundle(),
            new \DeskPRO\Bundle\ApiBundle\ApiBundle(),
            new \DeskPRO\Bundle\DevBundle\DevBundle(),
            new \DeskPRO\Bundle\SystemBundle\SystemBundle(),
            new \DeskPRO\Bundle\AuditBundle\AuditBundle(),
            new \DeskPRO\Bundle\AppStoreBundle\AppStoreBundle(),
            new \DeskPRO\Bundle\ReportBundle\ReportBundle(),
        ];

        return $bundles;
    }
}
