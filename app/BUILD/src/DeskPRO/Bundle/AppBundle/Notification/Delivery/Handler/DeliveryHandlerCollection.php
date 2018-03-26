<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryHandlerInterface;
use DeskPRO\Component\Util\AbstractCollection;

/**
 * Class DeliveryHandlerCollection.
 *
 * @property DeliveryHandlerInterface[] $collection
 */
class DeliveryHandlerCollection extends AbstractCollection
{
    /**
     * It's used to avoid looping when attaching handler to collection
     * Possibly you can say: "You can store keys inside collection", and you will right,
     * but who knows where and how it will be sorted?
     *
     * @var array an array of types
     */
    private $attached_types = [];

    /**
     * @param DeliveryHandlerInterface $handler
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function addHandler(DeliveryHandlerInterface $handler)
    {
        if ($this->hasHandler($handler->getType())) {
            throw new \InvalidArgumentException(sprintf('Handler with type [%s] already attached!', $handler->getType()));
        }

        $this->attached_types[$handler->getType()] = true;
        $this->collection[]                        = $handler;

        return $this;
    }

    /**
     * @param $handler_type
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function removeHandler($handler_type)
    {
        if (!$this->hasHandler($handler_type)) {
            throw new \InvalidArgumentException(sprintf('Trying to remove handler with type [%s] that wasn\'t attached', $handler_type));
        }

        unset($this->attached_types[$handler_type]);
        foreach ($this->collection as $key => $handler) {
            if ($handler_type === $handler->getType()) {
                unset($this->collection[$key]);
            }
        }

        return $this;
    }

    /**
     * @param $handler_type
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return DeliveryHandlerInterface
     */
    public function getHandler($handler_type)
    {
        if (!$this->hasHandler($handler_type)) {
            throw new \InvalidArgumentException(sprintf('Trying to get handler with type [%s] that wasn\'t attached'));
        }

        foreach ($this->collection as $handler) {
            if ($handler->getType() === $handler_type) {
                return $handler;
            }
        }

        throw new \LogicException('You should never reach here');
    }

    /**
     * @param string $handler_type
     *
     * @return bool
     */
    public function hasHandler($handler_type)
    {
        return array_key_exists($handler_type, $this->attached_types);
    }
}
