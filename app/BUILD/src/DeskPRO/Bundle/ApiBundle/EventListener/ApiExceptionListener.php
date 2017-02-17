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

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ApiExceptionListener.
 */
class ApiExceptionListener implements EventSubscriberInterface
{
    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @throws \Exception
     */
    public function onException(GetResponseForExceptionEvent $event)
    {
        $request   = $event->getRequest();
        $exception = $event->getException();

        $query = [];
        if ($request->query->has(JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM)) {
            $query[JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM] = 1;
        }

        try {
            $sub_request = $request->duplicate(
                $query,
                null,
                [
                    '_controller' => 'DeskPRO\Bundle\ApiBundle\Controller\ExceptionController::showAction',
                    'exception'   => $exception,
                ]
            );
            $sub_request->setMethod('GET');

            $response = $event->getKernel()->handle($sub_request, HttpKernel::SUB_REQUEST, false);
        } catch (\Exception $e) {
            if (!SystemErrorHandler::isProxyException($e)) {
                SystemErrorHandler::logException(new \Exception(sprintf(
                    'Exception thrown when handling an exception (%s: %s at %s line %s)',
                    get_class($e), $e->getMessage(), $e->getFile(), $e->getLine())
                ));
            }

            $wrapper = $e;

            while ($prev = $wrapper->getPrevious()) {
                if ($exception === $wrapper = $prev) {
                    throw $e;
                }
            }

            $prev = new \ReflectionProperty('Exception', 'previous');
            $prev->setAccessible(true);
            $prev->setValue($wrapper, $exception);

            throw $e;
        }

        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 128],
        ];
    }
}
