<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use DeskPRO\Kernel\KernelErrorHandler;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
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
        if ($exception instanceof InvalidFormException) {
            $errors_array = $this->generateFormErrors($exception->getForm());
        }

        if (!$exception instanceof InvalidFormException && !$exception instanceof HttpException) {
            KernelErrorHandler::handleException($exception);
        }

        $request = Request::createFromGlobals();

        // in dev environment, display a stack trace, dont show if we have a test.client
        if ($this->container->getParameter('kernel.debug') && !$this->container->has('test.client') && !$request->headers->has('x-agent-request')) {
            $error = new Response((string) $exception, 500);
            $error->headers->set('content-type', 'text/html');

            return $error;
        }

        $status  = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
        $code    = $this->getErrorCodeFactory()->getErrorCodeForException($exception);
        $message = $this->getErrorMessageFactory()->createMessage($code, $parameters);

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

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    private function generateFormErrors(FormInterface $form)
    {
        $errors = $list = [];
        foreach ($form->getErrors() as $error) {
            $code   = $this->getFormErrorCode($error);
            $list[] = [
                'code'    => $code,
                'message' => $this->getErrorMessageFactory()->createFormErrorMessage($code, $error),
            ];
        }

        if ($list) {
            $errors['errors'] = $list;
        }

        $children = [];
        foreach ($form->all() as $child) {
            if ($child instanceof FormInterface) {
                $child_errors = $this->generateFormErrors($child);
                if ($child_errors) {
                    $children[$child->getName()] = $child_errors;
                }
            }
        }

        // if it is NOT an associated array, we want to make it one
        if (
            !empty($children) // not empty
            && $this->needsPrefix($children)
        ) {
            $prefix       = !is_numeric($form->getName()) ? $form->getName().'_' : 'field_';
            $new_children = [];
            foreach ($children as $index => $value) {
                $new_children[$prefix.$index] = $value;
            }
            $children = $new_children;
        }

        if ($children) {
            $errors['fields'] = $children;
        }

        return $errors;
    }

    /**
     * @param array $children
     *
     * @return bool
     */
    protected function needsPrefix(array $children)
    {
        if (array_keys($children) === range(0, count($children) - 1)) {
            // indexed array, needs prefix
            return true;
        }

        foreach ($children as $key => $val) {
            if (!is_numeric($key)) {
                // any non-numeric key means no prefix
                return false;
            }
        }

        // if we get here all keys are numeric, so needs prefixing
        return true;
    }

    /**
     * @param FormError $error
     *
     * @return mixed|string
     */
    protected function getFormErrorCode(FormError $error)
    {
        return $this->getErrorCodeFactory()->getErrorCodeForFormError($error);
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Error\ErrorCodeFactory
     */
    protected function getErrorCodeFactory()
    {
        return $this->get('api_error.code_factory');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Error\ErrorMessageFactory
     */
    protected function getErrorMessageFactory()
    {
        return $this->get('api_error.message_factory');
    }
}
