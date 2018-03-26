<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ApiExceptionListener.
 */
class ApiExceptionListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 150],
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onException(GetResponseForExceptionEvent $event)
    {
        $request   = $event->getRequest();
        $exception = $event->getException();

        // api and ajax requests
        if (strpos($request->getPathInfo(), '/portal/api') !== false || $request->isXmlHttpRequest()) {
            if ($exception instanceof AccessDeniedException) {
                $event->setResponse(new JsonResponse(
                    [
                        'code'    => Response::HTTP_FORBIDDEN,
                        'message' => $exception->getMessage(),
                    ],
                    Response::HTTP_FORBIDDEN
                ));
            } elseif ($exception instanceof HttpException) {
                $event->setResponse(new JsonResponse(
                    [
                        'code'    => $exception->getStatusCode(),
                        'message' => $exception->getMessage(),
                    ],
                    $exception->getStatusCode()
                ));
            }
        }
    }
}
