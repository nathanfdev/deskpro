<?php

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
