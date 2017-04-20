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
