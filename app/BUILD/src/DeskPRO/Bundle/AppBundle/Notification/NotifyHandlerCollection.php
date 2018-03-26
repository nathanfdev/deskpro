<?php

namespace DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Component\Util\AbstractCollection;

/**
 * Class NotifyHandlerCollection.
 */
class NotifyHandlerCollection extends AbstractCollection
{
    /**
     * @var array
     */
    private $attached_handlers = [];

    /**
     * @param NotifyHandlerInterface $handler
     */
    public function attachHandler(NotifyHandlerInterface $handler)
    {
        if (!$this->hasHandler($handler)) {
            $this->attached_handlers[$handler->getType()] = true;
            $this->collection[]                           = $handler;
        } else {
            throw new \InvalidArgumentException(sprintf('Handler [ %s ] already exists!', $handler->getType()));
        }
    }

    /**
     * @param NotifyHandlerInterface $handler
     *
     * @return bool
     */
    protected function hasHandler(NotifyHandlerInterface $handler)
    {
        return array_key_exists($handler->getType(), $this->attached_handlers);
    }
}
