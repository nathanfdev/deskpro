<?php

namespace Application\LegacyApiBundle\Event;

use Application\LegacyApiBundle\Controller\AbstractController;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class Apiv1EndpointListener.
 */
class Apiv1EndpointListener implements EventSubscriberInterface
{
    /**
     * @var AuthorizationChecker
     */
    protected $checker;

    /**
     * @param AuthorizationChecker $checker
     */
    public function __construct(AuthorizationChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 64],
            KernelEvents::EXCEPTION  => ['onException', 64],
        ];
    }

    /**
     * @param FilterControllerEvent $event
     *
     * @throws AccessDeniedHttpException
     */
    public function onController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if ($controller instanceof AbstractController && !$this->checker->isGranted($controller[1], $controller[0])) {
            throw new AccessDeniedHttpException(
                'This is not endpoint you are looking for. Check permissions for your key or request mode.',
                null,
                42);
        }
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request   = $event->getRequest();

        if (
            (($exception instanceof AccessDeniedHttpException || $exception instanceof AccessDeniedException) && $request->isXmlHttpRequest())
            || strpos($request->getRequestUri(), '/api') === 0
        ) {
            $response = new JsonResponse();
            $response->headers->set('X-Status-Code', Response::HTTP_FORBIDDEN);
            $response->setContent([
                'error_code'    => $exception->getCode() === 42 ? 'insufficient_rights' : 'forbidden',
                'error_message' => $exception->getMessage(),
            ]);

            $event->setResponse($response);

            if (!($exception instanceof AccessDeniedHttpException || $exception instanceof AccessDeniedException || $exception instanceof NotFoundHttpException)) {
                SystemErrorHandler::logException($exception);
            }
        }
        if ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse();
            $response->headers->set('X-Status-Code', Response::HTTP_NOT_FOUND);
            $response->setContent([
                'error_code'    => 'not_found',
                'error_message' => $exception->getMessage(),
            ]);

            $event->setResponse($response);
        }
    }
}
