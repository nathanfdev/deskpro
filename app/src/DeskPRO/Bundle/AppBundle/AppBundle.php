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
namespace DeskPRO\Bundle\AppBundle;

use DeskPRO\Bundle\AppBundle\DependencyInjection\AppExtension;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\AppSecretPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\AssetPackagePass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\RegisterDataSerializerEventsPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\RegisterQuickSearchEventsPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\TermEnginePass;
use DeskPRO\Bundle\AppBundle\Security\Factory\AgentImpersonateFactory;
use DeskPRO\Bundle\AppBundle\Security\Factory\DpFormLoginFactory;
use DeskPRO\Bundle\AppBundle\Security\Factory\TransferSessionAuthFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new AppExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AppSecretPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new TermEnginePass());
        $container->addCompilerPass(new AssetPackagePass());
        $container->addCompilerPass(new RegisterDataSerializerEventsPass());
        $container->addCompilerPass(new RegisterQuickSearchEventsPass());

        /** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $security */
        $security = $container->getExtension('security');
        $security->addSecurityListenerFactory(new DpFormLoginFactory());
        $security->addSecurityListenerFactory(new AgentImpersonateFactory());
        $security->addSecurityListenerFactory(new TransferSessionAuthFactory());
    }

    public function registerCommands(Application $application)
    {
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return __DIR__;
    }
}
