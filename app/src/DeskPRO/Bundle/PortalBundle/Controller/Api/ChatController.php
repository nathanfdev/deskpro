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
use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatSystemEvent;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            return $this->generateFormErrorsResponse($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        $this->dispatch(
            UserChatEvent::SYSTEM_EVENT,
            new UserChatSystemEvent($conversation, UserChatSystemEvent::TYPE_STARTED, [], [
                'user_hidden' => true,
                'is_html'     => false,
            ])
        );

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
        $chat_message = new ChatMessage();
        $chat_message
            ->setOrigin('user')
            ->setContent($request->request->get('message'))
            ->setIsHtml(true)
            ->setMetadata([
                'is_html' => true,
            ])
        ;

        $this->sendMessage($conversation, $chat_message);

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

        $this->dispatch(
            UserChatEvent::SYSTEM_EVENT,
            new UserChatSystemEvent($conversation, UserChatSystemEvent::TYPE_END_BY_USER, [], ['chat_ended'])
        );

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
            return $this->generateFormErrorsResponse($form);
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

    /**
     * @param Form $form
     *
     * @return JsonResponse
     */
    protected function generateFormErrorsResponse(Form $form)
    {
        $generator = $this->get('api_error.form_errors_generator');
        $errors    = $generator->generateFormErrors($form);

        return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param ChatConversation $conversation
     * @param ChatMessage      $chat_message
     */
    protected function sendMessage(ChatConversation $conversation, ChatMessage $chat_message)
    {
        $conversation->addMessage($chat_message);

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        $channel = $conversation->getChannelId('newmessage');
        $this->dispatch(
            ClientMessageEvent::SEND_MESSAGE,
            new ClientMessageEvent($channel, $chat_message)
        );
    }

    /**
     * @param string $event_name
     * @param Event  $event
     */
    protected function dispatch($event_name, Event $event)
    {
        $this->get('event_dispatcher')->dispatch($event_name, $event);
    }
}
