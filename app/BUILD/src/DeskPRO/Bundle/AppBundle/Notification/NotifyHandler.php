<?php

namespace DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\MessageGeneratorCollection;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\MessageGeneratorInterface;

abstract class NotifyHandler implements NotifyHandlerInterface
{
    /**
     * @var MessageGeneratorCollection|MessageGeneratorInterface[]
     */
    protected $generators;

    public function __construct()
    {
        $this->generators = new MessageGeneratorCollection();
    }

    public function attachGenerator(MessageGeneratorInterface $generator)
    {
        $this->generators->addGenerator($generator);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return get_called_class();
    }
}
