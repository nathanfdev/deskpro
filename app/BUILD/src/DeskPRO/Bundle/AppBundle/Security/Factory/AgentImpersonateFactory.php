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

namespace DeskPRO\Bundle\AppBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Class AgentImpersonateFactory.
 */
class AgentImpersonateFactory extends AbstractFactory
{
    protected $defaultFailureHandlerOptions = [
        'failure_path'           => '/login?retry=auth',
        'failure_forward'        => false,
        'login_path'             => '/login',
        'failure_path_parameter' => '_failure_path',
    ];

    protected $defaultSuccessHandlerOptions = [
        'always_use_default_target_path' => true,
        'default_target_path'            => '/',
        'login_path'                     => '/login',
        'target_path_parameter'          => '_target_path',
        'use_referer'                    => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'agent_impersonate';
    }

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'dp_security.auth.provider.agent_impersonate.'.$id;
        $container->setDefinition($providerId, new DefinitionDecorator('dp_security.agent_impersonate.provider'));

        return $providerId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId()
    {
        return 'dp_security.form_login.listener';
    }
}
