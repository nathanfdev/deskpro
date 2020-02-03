<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Email;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CloudEmailTestController.
 *
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/cloud-email-test")
 */
class CloudEmailTestController extends BaseController
{
    /**
     * @Rest\Post("/send")
     *
     * @param Request $request
     *
     * @return View
     */
    public function sendOutgoingTestEmail(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['subject']) || !isset($data['to'])) {
            return View::create([
                'message' => 'Body must contain "subject" and "to" properties',
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var \Application\EmailBundle\SwiftMailer\Message\Message $message */
        $message = $this->container->getMailer()->createMessage();
        $message->setTo($data['to']);
        $message->setSubject($data['subject']);
        $message->setBody('TEST EMAIL');

        $this->container->getMailer()->queueMessage($message);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
