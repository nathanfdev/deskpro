<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StorageAdapterFactory.
 */
class StorageAdapterFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return StorageAdapterInterface
     */
    public static function createAdapter(ContainerInterface $container)
    {
        return new DbAdapter($container->get('doctrine.orm.voice_entity_manager'));
    }
}
