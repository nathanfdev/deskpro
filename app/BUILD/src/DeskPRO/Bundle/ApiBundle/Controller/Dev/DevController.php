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

namespace DeskPRO\Bundle\ApiBundle\Controller\Dev;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Dev\GenNotificationType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DevController.
 *
 * @ApiModes("all")
 * @Feature("dev")
 * @Rest\Route("/dev")
 * @ApiDoc(target="all", section="Dev")
 */
class DevController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Generates random notifications for specific agent",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     input="DeskPRO\Bundle\AppBundle\Form\Type\Dev\GenNotificationType",
     *     noOutput=true
     * )
     *
     * @Rest\Post("/gen_notifications")
     *
     * @param Request $request
     *
     * @return View
     */
    public function generateAgentNotificationsAction(Request $request)
    {
        $form = $this->createForm(GenNotificationType::class);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $generator = $this->get('dp.fixtures.random_agent_alert_generator');
        $person    = $form->get('agent')->getData();

        for ($i = 0; $i < 25; ++$i) {
            $generator->generateRandomAlert($person);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}
