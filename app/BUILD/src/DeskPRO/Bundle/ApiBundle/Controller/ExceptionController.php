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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\FormExceptionInterface;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use FOS\RestBundle\View\View;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Class ExceptionController.
 */
class ExceptionController extends BaseController
{
    /**
     * @param \Exception|FlattenException $exception
     *
     * @return View|Response
     */
    public function showAction($exception)
    {
        $parameters = [];
        if ($exception instanceof WrappedApiErrorException) {
            $parameters = $exception->getParams();
            $exception  = $exception->getException();
        }

        $errors_array = [];
        if ($exception instanceof FormExceptionInterface) {
            $errors_array = $this->get('form_error.form_errors_generator.api')->generateFormErrors($exception->getForm());
        } elseif ($exception instanceof ValidatorErrorsException) {
            $errors_array = $this->get('form_error.validator_errors_generator.api')->generateValidatorErrors($exception->getErrors());
        }

        // Log exceptions if in production
        if (!$exception instanceof FormExceptionInterface
            && !$exception instanceof HttpException
            && !$exception instanceof AuthenticationCredentialsNotFoundException) {
            if (!$this->container->getParameter('kernel.debug')) {
                $this->logException($exception);
            }
        }

        if ($exception instanceof AccessDeniedException || $exception instanceof AuthenticationCredentialsNotFoundException) {
            $exception = new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        $status = $exception instanceof HttpException ? $exception->getStatusCode() : 500;

        if ($exception instanceof \Exception) {
            $code    = $this->get('form_error.code_factory')->getErrorCodeForException($exception);
            $message = $this->get('form_error.message_factory.api')->createMessage($code, $parameters);
        } else {
            // this is a quick stub for dev mode.
            $code    = $exception->getCode();
            $message = $exception->getMessage();
        }

        // $exception has "getHeaders()" that we are interested in using

        $representation = $this->createErrorRepresentation(
            $status,
            $code,
            $message,
            $errors_array
        );

        $representation = $this->addExceptionInfo($exception, $representation);

        $headers = [];
        if ($exception instanceof HttpException) {
            $headers = $exception->getHeaders();
        }

        return View::create($representation, $status, $headers);
    }

    /**
     * @param \Exception|FlattenException $exception
     * @param array                       $representation
     *
     * @return array
     */
    private function addExceptionInfo($exception, array $representation)
    {
        // in dev environment, display a stack trace, dont show if we have a test.client
        if ($this->container->getParameter('kernel.debug') && !$this->container->has('test.client')) {
            $representation['exception'] = [
                'class'     => get_class($exception),
                'code'      => $exception->getCode(),
                'message'   => $exception->getMessage(),
                'file'      => $exception->getFile().':'.$exception->getLine(),
                'backtrace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        return $representation;
    }
}
