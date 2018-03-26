<?php

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
