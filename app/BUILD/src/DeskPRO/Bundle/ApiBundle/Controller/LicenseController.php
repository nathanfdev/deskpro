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

use Application\DeskPRO\Service\ErrorReporter;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\License\SupportRequestType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LicenseController.
 *
 * @ApiModes("all")
 * @Rest\Route("/license")
 */
class LicenseController extends BaseController
{
    /**
     * @Rest\Post("/send-support-request")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendSupportRequestAction(Request $request)
    {
        $form = $this->createForm(SupportRequestType::class, null, [
            'person' => $this->getUser(),
        ]);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $success = ErrorReporter::sendSupportMessage(
            $form->get('subject')->getData(),
            $form->get('message')->getData(),
            $form->get('name')->getData(),
            $form->get('email')->getData()
        );

        if (!$success) {
            throw $this->createBadRequestException('Unable to send request');
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
