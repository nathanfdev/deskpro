<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace Application\LegacyApiBundle;

use Application\DeskPRO\CustomFields\Form\FormHelper;
use Application\LegacyApiBundle\DependencyInjection\AccessDecisionPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LegacyApiBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function registerCommands(Application $application)
    {
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->register(FormHelper::class, FormHelper::class)
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('form.factory'))
        ;

        $container->registerExtension(new \Application\LegacyApiBundle\DependencyInjection\CoreExtension());
        $container->addCompilerPass(new AccessDecisionPass());
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
