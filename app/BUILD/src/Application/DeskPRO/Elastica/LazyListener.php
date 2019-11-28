<?php

namespace Application\DeskPRO\Elastica;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;

/**
 * This listener defers getting the real listener service until
 * it's actually used. This resolves circular depends:
 *
 * doctrine.dbal.default_connection =
 * -> fos_elastica.listener.deskpro.article
 * -> fos_elastica.object_persister.deskpro.article
 * -> fos_elastica.index.deskpro.article
 * -> fos_elastica.index.deskpro
 * -> deskpro.elastica.default_index_factory
 * -> fos_elastica.client.default
 * -> deskpro.core.settings
 *
 * See ElasticaClientPass for where this is configured.
 */
class LazyListener
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $serviceId;

    public function __construct(Container $container, $serviceId)
    {
        $this->container = $container;
        $this->serviceId = $serviceId;
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        return $this->container->get($this->serviceId)->postPersist($eventArgs);
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        return $this->container->get($this->serviceId)->postUpdate($eventArgs);
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        return $this->container->get($this->serviceId)->preRemove($eventArgs);
    }

    public function preFlush()
    {
        return $this->container->get($this->serviceId)->preFlush();
    }

    public function postFlush()
    {
        return $this->container->get($this->serviceId)->postFlush();
    }
}
