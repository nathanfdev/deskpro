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
namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ChatController.
 */
class ChatController extends AbstractController
{
    /**
     * @Route("/portal/api/chats/create", name="portal_api_chat_create")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createNewChatAction(Request $request)
    {
        $submitted_data = $request->request->all();

        $form = $this->get('form.factory')->createNamedBuilder(null, 'api_chat_create')->getForm();
        $form->submit($submitted_data);

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $conversation               = new ChatConversation();
        $conversation->person_name  = $submitted_data['name'];
        $conversation->person_email = $submitted_data['email'];

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        return new JsonResponse($conversation->getInfo());
    }

    /**
     * @Route("/portal/api/chats/{id}/polling", name="portal_api_chat_polling")
     * @Method({"GET"})
     *
     * @param ChatConversation $conversation
     *
     * @return JsonResponse
     */
    public function pollingChatAction(ChatConversation $conversation)
    {
        return new JsonResponse(array_merge($conversation->getInfo(), [
            'messages' => array_map(function (ChatMessage $message) {
                return [
                    'type'    => 'agent',
                    'message' => $message->content,
                ];
            }, $conversation->messages->toArray()),
        ]));
    }
}
