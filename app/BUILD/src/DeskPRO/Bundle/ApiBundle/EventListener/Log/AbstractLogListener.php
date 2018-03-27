<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener\Log;

use DeskPRO\Bundle\ApiBundle\Log\Helper\LogComposer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractLogListener implements EventSubscriberInterface
{
    /**
     * @var LogComposer
     */
    protected $composer;

    /**
     * @param LogComposer $log_composer
     */
    public function __construct(LogComposer $log_composer)
    {
        $this->composer = $log_composer;
    }
}
