<?php

namespace DeskPRO\Bundle\AppBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Class TransferSessionAuthFactory.
 */
class TransferSessionAuthFactory extends AbstractFactory
{
    protected $defaultFailureHandlerOptions = [
        'failure_path'           => '/login?retry=auth',
        'failure_forward'        => false,
        'login_path'             => '/login',
        'failure_path_parameter' => '_failure_path',
    ];

    protected $defaultSuccessHandlerOptions = [
        'always_use_default_target_path' => false,
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
        return 'transfer_session_auth';
    }

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'dp_security.auth.provider.transfer_session.'.$id;
        $container->setDefinition($providerId, new DefinitionDecorator('dp_security.transfer_session.provider'));

        return $providerId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId()
    {
        return 'dp_security.transfer_session.listener';
    }
}
