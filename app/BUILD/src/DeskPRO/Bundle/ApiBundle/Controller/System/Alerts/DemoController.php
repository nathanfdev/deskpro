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

namespace DeskPRO\Bundle\ApiBundle\Controller\System\Alerts;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class DemoController.
 *
 * @ApiDocSection("System")
 * @ApiModes("all")
 * @Annotations\Route("/system/demo")
 */
class DemoController extends BaseController
{
    const CONFIRM = 'I_understand_this_exists_for_testing_only';

    /**
     * @Get("/php-notice", requirements={"id"="\d+"})
     */
    public function phpNoticeAction(Request $request)
    {
        $this->checkConfirmation($request);

        $array = call_user_func(function () { return []; });
        $array['undefined_index'];

        return View::create($this->wrap('This action produced a PHP notice'), Response::HTTP_OK);
    }

    /**
     * @Get("/php-fatal-error", requirements={"id"="\d+"})
     */
    public function phpFatalErrorAction(Request $request)
    {
        $this->checkConfirmation($request);

        $null = call_user_func(function () { return; });
        new $null('This is a Fatal Error');

        return View::create($this->wrap('A PHP fatal error should have happened'), Response::HTTP_OK);
    }

    /**
     * @Get("/http-exception", requirements={"id"="\d+"})
     */
    public function httpExceptionAction(Request $request)
    {
        $this->checkConfirmation($request);
        throw $this->createBadRequestException('This is a Bad Request');
    }

    /**
     * @Get("/exception", requirements={"id"="\d+"})
     */
    public function exceptionAction(Request $request)
    {
        $this->checkConfirmation($request);
        throw new \Exception('Demo exception');
    }

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
