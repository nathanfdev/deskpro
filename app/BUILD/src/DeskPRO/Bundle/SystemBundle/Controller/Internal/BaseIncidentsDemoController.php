<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Controller\Internal;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class BaseIncidentsDemoController.
 *
 * This controller provides demo actions producing various system incidents such as PHP errors and exceptions. It's
 * meant to be used for production mode testing, that's why all actions require the corresponding ?confirm param.
 */
abstract class BaseIncidentsDemoController extends BaseController
{
    const CONFIRM = 'Yes_I_use_it_for_testing';

    /**
     * @Route("/php-notice", requirements={"id"="\d+"})
     */
    public function phpNoticeAction(Request $request)
    {
        $this->checkConfirmation($request);

        $array = call_user_func(function () {
            return [];
        });
        $array['undefined_index'];

        return $this->createResponse('This action produced a PHP notice');
    }

    /**
     * @Route("/php-fatal-error", requirements={"id"="\d+"})
     */
    public function phpFatalErrorAction(Request $request)
    {
        $this->checkConfirmation($request);

        $null = call_user_func(function () {
            return;
        });
        new $null('This is a Fatal Error');

        return $this->createResponse('A PHP fatal error should have happened');
    }

    /**
     * @Route("/http-exception", requirements={"id"="\d+"})
     */
    public function httpExceptionAction(Request $request)
    {
        $this->checkConfirmation($request);
        throw $this->createBadRequestException('This is a Bad Request');
    }

    /**
     * @Route("/exception", requirements={"id"="\d+"})
     */
    public function exceptionAction(Request $request)
    {
        $this->checkConfirmation($request);
        throw new \Exception('Demo exception');
    }

    /**
     * @param mixed $data
     *
     * @return Response
     */
    abstract protected function createResponse($data);

    /**
     * @param Request $request
     */
    private function checkConfirmation(Request $request)
    {
        if ($request->get('confirm') !== self::CONFIRM) {
            throw new AccessDeniedHttpException(
                'To access this action you need to provide correct confirm parameter value');
        }
    }
}
