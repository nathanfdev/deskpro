<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatBlock;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\EntityRepository\Department as DepartmentRepository;
use DeskPRO\Bundle\AppBundle\Entity\HitRecord;
use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatCreateType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatMessages;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat\ChatFeedbackType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat\ChatMessageType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat\ChatTranscriptInfoType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat\ChatTranscriptToggleType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat\ChatUserTypingType;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ChatController.
 *
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\ChatConversation": "DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\WidgetChat"
 * })
 * @Rest\Route("/portal/api/chats")
 */
class ChatController extends AbstractApiController
{
    /**
     * @Rest\Get("/custom_fields")
     *
     * @return View
     */
    public function getCustomFields()
    {
        $defs = $this->getManager()->getRepository(CustomDefChat::class)->findBy([
            'parent'     => null,
            'is_enabled' => 1,
        ]);

        return View::create($this->wrap($defs));
    }

    /**
     * @Rest\Post("/create")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function createNewChatAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // create legacy session
        $session = new Session();
        $session->setIpAddress($request->getClientIp());
        $session->setVisitorId($request->query->get('dp__v'));

        if ($this->getUser()) {
            $session->setPerson($this->getUser());
        }

        /** @var \Application\DeskPRO\EntityRepository\ChatBlock $repo */
        $repo  = $this->getDoctrine()->getRepository(ChatBlock::class);
        $block = $repo->getBlockForVisitor($session->getVisitorId(), $request->getClientIp());

        if ($block) {
            throw new AccessDeniedHttpException('banned');
        }

        $em->persist($session);
        $em->flush();

        // create a new chat conversation
        $conversation = ChatConversation::newForUserSession($session);

        /** @var DepartmentRepository $departmentRepository */
        $departmentRepository = $this->getManager()->getRepository(Department::class);
        $defaultDepartment    = $departmentRepository->getDefaultDepartment('chat');
        $conversation->setDepartment($defaultDepartment);

        $conversation->setBrand($this->getBrandContainer()->getBrand());

        // $clearMissing = false to check only submitted data
        // if chat in 'simple' mode so we can skip custom fields validation

        $form = $this->createForm(ChatCreateType::class, $conversation, [
            'person'     => $session->getPerson(),
            'visitor_id' => $session->getVisitorId(),
        ]);

        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $this->saveConversation($conversation);

        // If an email validation code was generated then user needs to validate the entered email first,
        // so skip agent notify until the user validates it
        if ($conversation->getEmailValidationCode()) {
            $this->dispatch(UserChatEvent::VALIDATE_EMAIL, new UserChatEvent($conversation));
        } else {
            // create a task to find an agent
            $task = $this->get('dp.voice.task_builder')->createChatTaskForQueue($conversation);
            $conversation->setTaskId($task->getId());
        }

        $this->setWidgetOption($this->getUser(), 'chat_id', $conversation->getAuthId());

        if ($session->getVisitorId()) {
            $hit = $this->getDoctrine()->getRepository(HitRecord::class)->findLastForVisitorId($session->getVisitorId());
            if ($hit && $hit->getUrl()) {
                $trackMsg = UserChatMessages::createUserTrackMessage($conversation, $hit->getUrl());
                $conversation->addMessage($trackMsg);

                $em->persist($trackMsg);
                $em->persist($conversation);
                $em->flush();

                $this->dispatch(UserChatEvent::USER_TRACK, new UserChatEvent($conversation, $trackMsg));
            }
        }

        return View::create($this->wrap($conversation));
    }

    /**
     * @Rest\Get("/{id}/polling")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function pollingChatAction(ChatConversation $conversation, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('m')
            ->from(ChatMessage::class, 'm')
            ->where(
                'm.conversation = :conversation_id',
                'm.id > :last_message_id',
                'm.is_user_hidden = false'
            )
            ->setParameters([
                'conversation_id' => $conversation->getId(),
                'last_message_id' => $request->get('last_message_id', 0),
            ])
        ;

        $this->dispatch(UserChatEvent::POLLING, new UserChatEvent($conversation));
        if (!$conversation->getAgent()) {
            $this->get('dp.voice.task_router')->evaluate();
        }

        return View::create([
            'chat_info'    => $this->wrap($conversation),
            'new_messages' => $this->wrap($qb->getQuery()->getResult()),
        ]);
    }

    /**
     * @Rest\Post("/{id}/messages")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function sendMessageAction(ChatConversation $conversation, Request $request)
    {
        $form = $this->createForm(ChatMessageType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $chatMessages = [];

        // Add message to chat conversation
        $content = $form->get('message')->getData();
        if (is_string($content) && strip_tags($content)) {
            $chatMessage = UserChatMessages::createUserTextMessage($conversation, $content);

            $conversation->addMessage($chatMessage);
            $chatMessages[] = $chatMessage;
        }

        $serializer = $this->get('serializer');

        // Add blobs to chat conversation
        /** @var Blob[] $attachments */
        $attachments = $form->get('attachments')->getData();
        foreach ($attachments as $attachment) {
            // Support old attachment message format
            $content = sprintf(
                'File: <a href="%s" target="_blank">%s</a> (%s)',

                $attachment->getDownloadUrl(true),
                htmlspecialchars($attachment->filename),
                $attachment->getReadableFilesize()
            );

            if ($attachment->isImage()) {
                $content .= sprintf('<div class="file-thumb"><img src="%s" /></div>', $attachment->getThumbnailUrl(50, true));
            }

            $chatMessage = UserChatMessages::createUserTextMessage($conversation, $content);
            $chatMessage->setMetadata(array_merge($chatMessage->getMetadata(), [
                'type'    => 'file',
                'blob_id' => $attachment->getId(),
                'blob'    => $serializer->toArray($attachment, new SideloadSerializationContext()),
            ]));

            $conversation->addMessage($chatMessage);
            $chatMessages[] = $chatMessage;
        }

        $this->saveConversation($conversation);

        // Dispatch send message event after saving chat conversation to get message ids
        foreach ($chatMessages as $chatMessage) {
            $this->dispatch(UserChatEvent::SEND_MESSAGE, new UserChatEvent($conversation, $chatMessage));
        }

        return View::create($this->wrap($chatMessages));
    }

    /**
     * @Rest\Post("/{id}/ack_messages")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function ackMessagesAction(ChatConversation $conversation, Request $request)
    {
        $messageIds  = $request->request->get('message_ids');
        $currentDate = new \DateTime();

        if (!empty($messageIds)) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb
                ->update(ChatMessage::class, 'cm')
                ->set('cm.date_received', ':date_received')
                ->where(
                    'cm.id IN(:message_ids)',
                    'cm.conversation = :conversation_id'
                )
                ->setParameters([
                    'date_received'   => $currentDate->format('c'),
                    'message_ids'     => $messageIds,
                    'conversation_id' => $conversation->getId(),
                ])
            ;

            $qb->getQuery()->execute();
            $this->dispatch(UserChatEvent::ACK_MESSAGES, new UserChatEvent($conversation, $messageIds));
        }

        return View::create();
    }

    /**
     * @Rest\Post("/{id}/user_typing")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function userTypingAction(ChatConversation $conversation, Request $request)
    {
        $form = $this->createForm(ChatUserTypingType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $partialMessage = $form->get('partial_message')->getData();
        $this->dispatch(UserChatEvent::USER_TYPING, new UserChatEvent($conversation, $partialMessage));

        return View::create();
    }

    /**
     * @Rest\Post("/{id}/transcript/info")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function sendTranscriptInfoAction(ChatConversation $conversation, Request $request)
    {
        $form = $this->createForm(ChatTranscriptInfoType::class, $conversation);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $this->saveConversation($conversation);

        return View::create();
    }

    /**
     * @Rest\Post("/{id}/transcript/toggle")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function toggleShouldSendTranscriptAction(ChatConversation $conversation, Request $request)
    {
        $form = $this->createForm(ChatTranscriptToggleType::class, $conversation);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $this->saveConversation($conversation);

        return View::create();
    }

    /**
     * @Rest\Post("/{id}/end")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     *
     * @return View
     */
    public function endChatAction(ChatConversation $conversation)
    {
        $conversation
            ->setStatus(ChatConversation::STATUS_ENDED)
            ->setEndedBy('user')
        ;

        $this->saveConversation($conversation);
        $this->setWidgetOption($this->getUser(), 'chat_id', null);

        // cancel task
        $this->dispatch(UserChatEvent::END_BY_USER, new UserChatEvent($conversation, [], ['chat_ended']));
        $this->get('dp.voice.task_router')->cancelTask($conversation->getTaskId());

        return View::create();
    }

    /**
     * @Rest\Post("/{id}/reopen")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     *
     * @return View
     */
    public function reopenChatAction(ChatConversation $conversation)
    {
        $conversation
            ->setStatus(ChatConversation::STATUS_OPEN)
            ->setEndedBy(null)
            ->setDateEnded(null)
            ->setShouldSendTranscript(false)
            ->setDateTranscriptSent(null)
        ;

        $this->saveConversation($conversation);
        $this->dispatch(UserChatEvent::USER_RETURNED, new UserChatEvent($conversation));
        $this->setWidgetOption($this->getUser(), 'chat_id', $conversation->getId());

        return View::create();
    }

    /**
     * @Rest\Post("/{id}/find_agent")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     *
     * @return View
     */
    public function findAnotherAgentAction(ChatConversation $conversation)
    {
        if (!$conversation->getAgent()) {
            $this->dispatch(UserChatEvent::STARTED, new UserChatEvent($conversation));
        }

        return View::create();
    }

    /**
     * @Rest\Post("/{id}/feedback")
     * @ParamConverter(converter="portal_api_chat")
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function feedbackAction(ChatConversation $conversation, Request $request)
    {
        $form = $this->createForm(ChatFeedbackType::class, $conversation);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $this->saveConversation($conversation);

        return View::create();
    }

    /**
     * @param ChatConversation $conversation
     */
    protected function saveConversation(ChatConversation $conversation)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();
    }
}
