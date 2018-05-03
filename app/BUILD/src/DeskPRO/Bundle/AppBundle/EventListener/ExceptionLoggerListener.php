<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Class ExceptionLoggerListener.
 */
class ExceptionLoggerListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @throws \DeskPRO\Bundle\SystemBundle\SystemAlerts\LoggerException
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        SystemErrorHandler::logException($event->getException());
    }
}
