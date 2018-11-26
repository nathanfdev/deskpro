<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\AbuseCaptchaFormException;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\FormExceptionInterface;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use FOS\RestBundle\View\View;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

        if ($exception instanceof AbuseCaptchaFormException) {
            return $this->createCaptchaResponse($exception);
        }

        $errorsArray = $this->getErrorsArray($exception);

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

        if ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();
        } elseif (method_exists($exception, 'getStatusCode') && isset(Response::$statusTexts[$exception->getStatusCode()])) {
            $status = $exception->getStatusCode();
        } elseif ($exception->getCode() && isset(Response::$statusTexts[$exception->getCode()])) {
            $status = $exception->getCode();
        } else {
            $status = 500;
        }

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
            $errorsArray
        );

        $representation = $this->addExceptionInfo($exception, $representation);

        $headers = [];
        if ($exception instanceof HttpException) {
            $headers = $exception->getHeaders();
        }

        return View::create($representation, $status, $headers);
    }

    /**
     * @param $exception
     *
     * @return array
     */
    protected function getErrorsArray($exception)
    {
        $errorsArray = [];
        if ($exception instanceof FormExceptionInterface) {
            $errorsArray = $this->get('form_error.form_errors_generator.api')->generateFormErrors($exception->getForm());
        } elseif ($exception instanceof ValidatorErrorsException) {
            $errorsArray = $this->get('form_error.validator_errors_generator.api')->generateValidatorErrors($exception->getErrors());
        }

        return $errorsArray;
    }

    /**
     * @param \Exception|FlattenException $exception
     * @param array                       $representation
     *
     * @return array
     */
    protected function addExceptionInfo($exception, array $representation)
    {
        // in dev environment, display a stack trace, dont show if we have a test.client
        if ($this->container->getParameter('kernel.debug') && !$this->container->has('test.client')) {
            if ($exception instanceof FlattenException) {
                $backtrace = $exception->getTrace();
            } else {
                $backtrace = explode("\n", $exception->getTraceAsString());
            }
            $representation['exception'] = [
                'class'     => get_class($exception),
                'code'      => $exception->getCode(),
                'message'   => $exception->getMessage(),
                'file'      => $exception->getFile().':'.$exception->getLine(),
                'backtrace' => $backtrace,
            ];
        }

        return $representation;
    }

    /**
     * @param AbuseCaptchaFormException $exception
     *
     * @return View
     */
    protected function createCaptchaResponse(AbuseCaptchaFormException $exception)
    {
        $token   = DpStrings::random(20, Strings::CHARS_KEY_ALPHA);
        $message = $this->container->get('form_error.message_factory.api')->createMessage($exception->getMessage());

        return View::create(
            [
                'code'              => $exception->getMessage(),
                'message'           => $message,
                'captcha_token'     => $token,
                'captcha_image_url' => $this->get('router')->generate(
                    'gregwar_captcha.generate_api_captcha',
                    [
                        'token' => $token,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            $exception->getStatusCode()
        );
    }
}
