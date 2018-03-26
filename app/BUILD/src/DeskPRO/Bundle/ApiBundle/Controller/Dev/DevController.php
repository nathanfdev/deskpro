<?php

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
