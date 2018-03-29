<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * @ApiModes("all")
 */
class ExceptionController extends AbstractController
{
    public function showAction(FlattenException $exception, \Symfony\Component\HttpKernel\Log\DebugLoggerInterface $logger = null, $format = 'html')
    {
        return $this->createApiErrorResponse(
            'http_error.'.$exception->getStatusCode(),
            $exception->getMessage(),
            $exception->getStatusCode()
        );
    }
}
