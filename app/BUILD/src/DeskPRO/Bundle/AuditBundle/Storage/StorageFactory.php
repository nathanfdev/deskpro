<?php

namespace DeskPRO\Bundle\AuditBundle\Storage;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StorageFactory.
 */
class StorageFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * StorageFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return object
     */
    public function createStorage()
    {
        $type      = $this->container->get('settings_resolver')->getGlobalSettings()->get('audit_log.storage');
        $storageId = 'audit_log.storage.'.$type;
        if (!$this->container->has($storageId)) {
            throw new \RuntimeException(sprintf('Couldn\'t find audit log storage with type [ %s ]', $type));
        }

        return $this->container->get($storageId);
    }

    /**
     * @return object
     */
    public function createTransformer()
    {
        $type          = $this->container->get('settings_resolver')->getGlobalSettings()->get('audit_log.storage');
        $transformerId = 'audit_log.transformer.'.$type;
        if (!$this->container->has($transformerId)) {
            throw new \RuntimeException(sprintf('Couldn\'t find audit log transformer with type [ %s ]', $type));
        }

        return $this->container->get($transformerId);
    }
}
