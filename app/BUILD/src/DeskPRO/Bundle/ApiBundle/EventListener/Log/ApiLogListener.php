<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\EventListener\Log;

use DeskPRO\Bundle\ApiBundle\Log\Helper\LogHelper;
use DeskPRO\Bundle\ApiBundle\Log\LogSaveException;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ApiLogListener.
 */
class ApiLogListener extends AbstractLogListener
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 31],
            // the priority doesn't make sense because we are using DP_START_TIME, that defined
            // at the very beginning of request handling
            // so you have to be sure, that it will run AFTER Auth and RequestIdListener
            KernelEvents::REQUEST => ['onRequest', -32],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->getLogHelper()->isLoggingEnabled() && $event->isMasterRequest()) {
            $this->composer->createApiLog($request);
        }

        return;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if ($event->isMasterRequest() && $this->composer->getLog() && $this->composer->getLog()->getMode()) {
            $this->composer->finishApiLog($event->getResponse());
            try {
                $this->composer->write($event->getRequest(), $event->getResponse());
            } catch (LogSaveException $e) {
                throw new ConflictHttpException('Error saving log entry, possibly request with given ID already processed');
            }
        }
    }

    /**
     * @return LogHelper
     */
    protected function getLogHelper()
    {
        return $this->composer->getLogHelper();
    }
}
