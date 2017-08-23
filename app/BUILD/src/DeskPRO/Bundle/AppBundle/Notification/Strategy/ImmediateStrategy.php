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

namespace DeskPRO\Bundle\AppBundle\Notification\Strategy;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistenceAdapterInterface;

/**
 * Class ImmediateStrategy.
 */
class ImmediateStrategy extends AbstractStrategy
{
    /**
     * @param SystemEventInterface $event
     */
    public function handleSystemEvent(SystemEventInterface $event)
    {
        $messages = $this->createMessages($event);
        foreach ($messages as $message) {
            $this->deliveryService->schedule($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deliver($postpone = false)
    {
        $this->deliveryService->deliver($postpone);
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    protected function createMessages(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->eventHandlers as $handler) {
            /** @var NotifyHandlerInterface $handler */
            $messages = array_merge($messages, $handler->processEvent($event));
        }

        return $messages;
    }

    /**
     * For immediate strategy it's just a stub - no need to persist anything.
     *
     * @param PersistenceAdapterInterface $persistenceAdapter
     *
     * @return $this
     */
    public function setPersistenceAdapter(PersistenceAdapterInterface $persistenceAdapter)
    {
        return $this;
    }
}
