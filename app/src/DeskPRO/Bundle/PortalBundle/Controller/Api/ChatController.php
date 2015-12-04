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
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManager;
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
        $conversation = new ChatConversation();
        $form         = $this->get('form.factory')->createNamedBuilder(null, 'api_chat_create', $conversation)->getForm();
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        return new JsonResponse($this->dataSerialize($conversation));
    }

    /**
     * @Route("/portal/api/chats/{id}/polling", name="portal_api_chat_polling")
     * @Method({"GET"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return JsonResponse
     */
    public function pollingChatAction(ChatConversation $conversation, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('m')
            ->from('DeskPRO:ChatMessage', 'm')
            ->where(
                'm.conversation = :conversation_id',
                'm.id > :last_message_id'
            )
            ->setParameters([
                'conversation_id' => $conversation->getId(),
                'last_message_id' => $request->get('last_message_id', 0),
            ])
        ;

        return new JsonResponse([
            'chat_info'    => $this->dataSerialize($conversation),
            'new_messages' => $this->dataSerialize($qb->getQuery()->getResult()),
        ]);
    }

    /**
     * @Route("/portal/api/chats/{id}/messages", name="portal_api_chat_message")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return JsonResponse
     */
    public function sendMessageAction(ChatConversation $conversation, Request $request)
    {
        $message          = new ChatMessage();
        $message->content = $request->request->get('message');

        $conversation->addMessage($message);

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * @Route("/portal/api/chats/{id}/end", name="portal_api_chat_end")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     *
     * @return JsonResponse
     */
    public function endChatAction(ChatConversation $conversation)
    {
        $conversation->setStatus(ChatConversation::STATUS_ENDED);

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * @Route("/portal/api/chats/{id}/reopen", name="portal_api_chat_reopen")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     *
     * @return JsonResponse
     */
    public function reopenChatAction(ChatConversation $conversation)
    {
        $conversation->setStatus(ChatConversation::STATUS_OPEN);

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * @Route("/portal/api/chats/{id}/feedback", name="portal_api_chat_feedback")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return JsonResponse
     */
    public function feedbackAction(ChatConversation $conversation, Request $request)
    {
        $form = $this->get('form.factory')->createNamedBuilder(null, 'api_chat_feedback', $conversation)->getForm();
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function dataSerialize($data)
    {
        return $this->get('data_serializer')->serialize($data);
    }
}
