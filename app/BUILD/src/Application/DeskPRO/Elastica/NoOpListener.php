<?php

namespace Application\DeskPRO\Elastica;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;

/**
 * This listener is a noop. Elastica bundle initialises
 * several listeners that we don't use because we handle
 * it ourselves in deskpro.search.entity_listener
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
class NoOpListener
{
    public function __construct()
    {
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
    }

    public function preFlush()
    {
    }

    public function postFlush()
    {
    }
}
