<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Persistance;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;

/**
 * Class PersistenceAdapterInterface.
 */
interface PersistenceAdapterInterface
{
    public function persist(SystemEventInterface $event);
}
