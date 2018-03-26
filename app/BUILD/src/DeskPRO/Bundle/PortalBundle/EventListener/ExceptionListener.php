<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ExceptionListener.
 */
class ExceptionListener extends \Symfony\Component\HttpKernel\EventListener\ExceptionListener
{
    /**
     * {@inheritdoc}
     */
    protected function logException(\Exception $exception, $message)
    {
        if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() < 500) {
            return;
        }
        if ($exception instanceof AccessDeniedException) {
            return;
        }

        parent::logException($exception, $message);
    }
}
