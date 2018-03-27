<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DeliveryHandlerCollection;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

/**
 * Class DeliveryService.
 */
class DeliveryService
{
    /**
     * @var DeliveryHandlerCollection
     */
    protected $collection;

    /**
     * @var bool
     */
    protected $cliProcess;

    /**
     * @var bool
     */
    protected $batchMode = false;

    /**
     * @var bool
     */
    protected $defaultBatchMode = true;

    /**
     * DeliveryService constructor.
     *
     * @param bool $cliProcess
     */
    public function __construct($cliProcess = false)
    {
        $this->cliProcess       = $cliProcess;
        $this->batchMode        = !$cliProcess; // if cli then should be no batch until set manually
        $this->defaultBatchMode = !$cliProcess;
        $this->collection       = new DeliveryHandlerCollection();
    }

    /**
     * @param MessageInterface $message
     */
    public function schedule(MessageInterface $message)
    {
        foreach ($this->collection as $handler) {
            /* @var DeliveryHandlerInterface $handler */
            $handler->schedule($message);
        }
        if ($this->cliProcess && !$this->batchMode) {
            $this->deliver();
        }
    }

    public function deliver($postpone = false)
    {
        foreach ($this->collection as $handler) {
            /* @var DeliveryHandlerInterface $handler */
            if ($postpone) {
                $handler->deliverSoon();
            } else {
                $handler->deliver();
            }
        }
    }

    /**
     * @return $this
     */
    public function startBatch()
    {
        $this->batchMode = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function stopBatch()
    {
        $this->deliver();
        $this->batchMode = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function resetBatch()
    {
        $this->batchMode = $this->defaultBatchMode;
        $this->deliver();

        return $this;
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
    public function attachHandler(DeliveryHandlerInterface $handler)
    {
        $this->collection->addHandler($handler);

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
    public function detachHandler(DeliveryHandlerInterface $handler)
    {
        $this->collection->removeHandler($handler->getType());

        return $this;
    }
}
