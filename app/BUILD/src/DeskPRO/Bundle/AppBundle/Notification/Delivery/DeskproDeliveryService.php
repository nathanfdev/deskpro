<?php

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
