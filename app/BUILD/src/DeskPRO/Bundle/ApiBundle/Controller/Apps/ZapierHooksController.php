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

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ZapierHook;
use DeskPRO\Bundle\AppBundle\Form\Type\ZapierHookType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class PeopleController.
 *
 * @ApiModes("all")
 * @Rest\Route("/apps/zapier/hooks")
 * @ApiDoc(target="deleteAction", section="Apps")
 */
class ZapierHooksController extends CrudController
{
    public static $entity = ZapierHook::class;
    public static $type   = ZapierHookType::class;

    /**
     * Allow Zapier to subscribe to hooks.
     *
     * @ApiDoc(
     *     section="Apps",
     *     description="Called by Zapier when subscribing to hooks",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View|BadRequestHttpException
     */
    public function postAction(Request $request)
    {
        $zapierHook = new ZapierHook();

        $this->handleForm($zapierHook, $request);

        return View::create(['id' => $zapierHook->getId()]);
    }
}
