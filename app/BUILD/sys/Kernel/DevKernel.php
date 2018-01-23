<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
