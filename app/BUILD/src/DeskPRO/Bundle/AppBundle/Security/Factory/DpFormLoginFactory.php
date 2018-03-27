<?php

namespace DeskPRO\Bundle\AppBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DpFormLoginFactory.
 */
class DpFormLoginFactory extends AbstractFactory
{
    protected $defaultFailureHandlerOptions = [
        'failure_path'           => '/login?retry=auth',
        'failure_forward'        => false,
        'login_path'             => '/login',
        'failure_path_parameter' => '_failure_path',
    ];

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'deskpro_form_login';
    }

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'dp_security.auth.provider.form_login.'.$id;
        $container->setDefinition($providerId, new DefinitionDecorator('dp_security.form_login.provider'));

        return $providerId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId()
    {
        return 'dp_security.form_login.listener';
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'security.authentication.dp_form_entry_point.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('security.authentication.form_entry_point'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($config['login_path'])
            ->addArgument($config['use_forward']);

        return $entryPointId;
    }
}
