<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\FormExceptionInterface;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use DpSys\LowError\SystemErrorHandler;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ExceptionController.
 */
class ExceptionController extends BaseController
{
    /**
     * @param \Exception $exception
     *
     * @return View|Response
     */
    public function showAction(\Exception $exception)
    {
        $parameters = [];
        if ($exception instanceof WrappedApiErrorException) {
            $parameters = $exception->getParams();
            $exception  = $exception->getException();
        }

        $errors_array = [];
        if ($exception instanceof FormExceptionInterface) {
            $errors_array = $this->get('api_error.form_errors_generator')->generateFormErrors($exception->getForm());
        } elseif ($exception instanceof ValidatorErrorsException) {
            $errors_array = $this->get('api_error.validator_errors_generator')->generateValidatorErrors($exception->getErrors());
        }

        if (!$exception instanceof FormExceptionInterface && !$exception instanceof HttpException) {
            SystemErrorHandler::handleException($exception);
        }

        $request = Request::createFromGlobals();

        // in dev environment, display a stack trace, dont show if we have a test.client
        if ($this->container->getParameter('kernel.debug') && !$this->container->has('test.client') && !$request->headers->has('x-agent-request')) {
            $error = new Response((string) $exception, 500);
            $error->headers->set('content-type', 'text/html');

            return $error;
        }

        $status  = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
        $code    = $this->get('api_error.code_factory')->getErrorCodeForException($exception);
        $message = $this->get('api_error.message_factory')->createMessage($code, $parameters);

        // $exception has "getHeaders()" that we are interested in using

        $representation = $this->createErrorRepresentation(
            $status,
            $code,
            $message,
            $errors_array
        );

        $headers = [];
        if ($exception instanceof HttpException) {
            $headers = $exception->getHeaders();
        }

        return View::create($representation, $status, $headers);
    }
}
