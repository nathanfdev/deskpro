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

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DeliveryHandlerCollection;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

class DeskproDeliveryService extends DeliveryService
{
    /**
     * @var DeliveryHandlerCollection
     */
    private $targettedHandlers;

    public function __construct($cliProcess)
    {
        parent::__construct($cliProcess);
        $this->targettedHandlers = new DeliveryHandlerCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(MessageInterface $message)
    {
        parent::schedule($message);

        if ($message instanceof ActionAlert) {
            $meta = $message->getMetaData();
            if (!empty($meta['targettedHandlers'])) {
                foreach ($meta['targettedHandlers'] as $handlerType) {
                    if ($this->targettedHandlers->hasHandler($handlerType) && !$this->collection->hasHandler($handlerType)) {
                        $handler = $this->targettedHandlers->getHandler($handlerType);
                        $handler->schedule($message);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deliver($postpone = false)
    {
        parent::deliver($postpone);
        foreach ($this->targettedHandlers as $handler) {
            /* @var DeliveryHandlerInterface $handler */
            if ($postpone) {
                $handler->deliverSoon();
            } else {
                $handler->deliver();
            }
        }
    }

    /**
     * It's just a proxy method.
     *
     * @param DeliveryHandlerInterface $handler
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function attachTargettedHandler(DeliveryHandlerInterface $handler)
    {
        $this->targettedHandlers->addHandler($handler);

        return $this;
    }

    /**
     * @param DeliveryHandlerInterface $handler
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return $this
     */
    public function detachTargettedHandler(DeliveryHandlerInterface $handler)
    {
        $this->targettedHandlers->removeHandler($handler->getType());

        return $this;
    }
}
