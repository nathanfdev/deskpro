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

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ChatController.
 */
class ChatController extends AbstractApiController
{
    /**
     * @Route("/portal/api/chats/create", name="portal_api_chat_create")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function createNewChatAction(Request $request)
    {
        $this->checkRequireLogin($request);

        $session      = $this->getApiSession($request);
        $conversation = ChatConversation::newForUserSession($session);
        $form         = $this
            ->get('form.factory')
            ->createNamedBuilder(null, 'api_chat_create', $conversation, ['person' => $session->getPerson()])
            ->getForm()
        ;

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $this->saveConversation($conversation);

        // If an email validation code was generated then user needs to validate the entered email first,
        // so skip agent notify until the user validates it
        if ($conversation->getEmailValidationCode()) {
            $this->dispatch(UserChatEvent::VALIDATE_EMAIL, new UserChatEvent($conversation));
        } else {
            $this->dispatch(UserChatEvent::STARTED, new UserChatEvent($conversation));
        }

        return View::create($this->dataSerialize($conversation));
    }

    /**
     * @Route("/portal/api/chats/{id}/validate/email/regenerate", name="portal_api_chat_validate_email_regenerate")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function regenerateEmailValidationCodeAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $conversation->regenerateEmailValidationCode();

        $this->saveConversation($conversation);
        $this->dispatch(UserChatEvent::VALIDATE_EMAIL, new UserChatEvent($conversation));

        return View::create();
    }

    /**
     * @Route("/portal/api/chats/{id}/validate/email", name="portal_api_chat_validate_email")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function validateEmailAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $form = $this
            ->get('form.factory')
            ->createNamedBuilder(null, 'api_chat_validate_email', $conversation)
            ->getForm()
        ;

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $this->saveConversation($conversation);
        $this->dispatch(UserChatEvent::STARTED, new UserChatEvent($conversation));

        return View::create();
    }

    /**
     * @Route("/portal/api/chats/{id}/polling", name="portal_api_chat_polling")
     * @Method({"GET"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function pollingChatAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

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

        return View::create([
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
     * @return View
     */
    public function sendMessageAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $form = $this
            ->get('form.factory')
            ->createNamedBuilder(null, 'api_chat_message')
            ->getForm()
        ;

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $chat_messages = [];

        // Add message to chat conversation
        $content = $form->get('message')->getData();
        if ($content) {
            $chat_message = new ChatMessage();
            $chat_message
                ->setOrigin('user')
                ->setAuthor($conversation->getPerson())
                ->setContent($content)
                ->setIsUser(true)
                ->setIsHtml(true)
                ->setMetadata([
                    'is_html'         => true,
                    'is_user_message' => true,
                ])
            ;

            $conversation->addMessage($chat_message);
            $this->dispatch(UserChatEvent::SEND_MESSAGE, new UserChatEvent($conversation, $chat_message));

            $chat_messages[] = $chat_message;
        }

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
                $content .= sprintf(
                    '<div class="file-thumb"><img src="%s" /></div>',
                    $attachment->getThumbnailUrl(50, true)
                );
            }

            $chat_message = new ChatMessage();
            $chat_message
                ->setOrigin('user')
                ->setAuthor($conversation->getPerson())
                ->setContent($content)
                ->setIsUser(true)
                ->setIsHtml(true)
                ->setMetadata([
                    'is_html'         => true,
                    'type'            => 'file',
                    'blob_id'         => $attachment->getId(),
                    'blob'            => $this->dataSerialize($attachment)['data'],
                    'is_user_message' => true,
                ])
            ;

            $conversation->addMessage($chat_message);
            $this->dispatch(UserChatEvent::SEND_MESSAGE, new UserChatEvent($conversation, $chat_message));

            $chat_messages[] = $chat_message;
        }

        $this->saveConversation($conversation);

        return View::create($this->dataSerialize($chat_messages));
    }

    /**
     * @Route("/portal/api/chats/{id}/ack_messages", name="portal_api_chat_ack_messages")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function ackMessagesAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $message_ids  = $request->request->get('message_ids');
        $current_date = new \DateTime();

        if (!empty($message_ids)) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb
                ->update('DeskPRO:ChatMessage', 'cm')
                ->set('cm.date_received', ':date_received')
                ->where(
                    'cm.id IN(:message_ids)',
                    'cm.conversation = :conversation_id'
                )
                ->setParameters([
                    'date_received'   => $current_date->format('c'),
                    'message_ids'     => $message_ids,
                    'conversation_id' => $conversation->getId(),
                ])
            ;

            $qb->getQuery()->execute();
            $this->dispatch(UserChatEvent::ACK_MESSAGES, new UserChatEvent($conversation, $message_ids));
        }

        return View::create();
    }

    /**
     * @Route("/portal/api/chats/{id}/user_typing", name="portal_api_chat_user_typing")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function userTypingAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $form = $this
            ->get('form.factory')
            ->createNamedBuilder(null, 'api_chat_user_typing')
            ->getForm()
        ;

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $partial_message = $form->get('partial_message')->getData();
        $this->dispatch(UserChatEvent::USER_TYPING, new UserChatEvent($conversation, $partial_message));

        return View::create();
    }

    /**
     * @Route("/portal/api/chats/{id}/transcript_info", name="portal_api_chat_transcript_info")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function sendTranscriptInfoAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $form = $this
            ->get('form.factory')
            ->createNamedBuilder(null, 'api_chat_transcription_info', $conversation)
            ->getForm()
        ;

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $this->saveConversation($conversation);

        return View::create();
    }

    /**
     * @Route("/portal/api/chats/{id}/transcript_data", name="portal_api_chat_transcript_data")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function sendTranscriptDataAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $already_sent = $conversation->getShouldSendTranscript();
        $person       = $conversation->getPerson();
        $has_email    = $person ? $person->getPrimaryEmailAddress() : $conversation->getPersonEmail();
        $has_answer   = $conversation->getDateFirstAgentMessage();

        $can_send = !$already_sent && $has_email && $has_answer;
        if ($can_send) {
            $conversation->setShouldSendTranscript(true);
            $this->saveConversation($conversation);
        }

        return View::create([
            'success' => $can_send,
        ]);
    }

    /**
     * @Route("/portal/api/chats/{id}/end", name="portal_api_chat_end")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function endChatAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);
        $conversation->setStatus(ChatConversation::STATUS_ENDED);

        $this->saveConversation($conversation);
        $this->dispatch(UserChatEvent::END_BY_USER, new UserChatEvent($conversation, [], ['chat_ended']));

        return View::create();
    }

    /**
     * @Route("/portal/api/chats/{id}/reopen", name="portal_api_chat_reopen")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function reopenChatAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $conversation
            ->setStatus(ChatConversation::STATUS_OPEN)
            ->setEndedBy(null)
            ->setDateEnded(null)
            ->setShouldSendTranscript(false)
            ->setDateTranscriptSent(null)
        ;

        $this->saveConversation($conversation);
        $this->dispatch(UserChatEvent::USER_RETURNED, new UserChatEvent($conversation));

        return View::create();
    }

    /**
     * @Route("/portal/api/chats/{id}/feedback", name="portal_api_chat_feedback")
     * @Method({"POST"})
     *
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @return View
     */
    public function feedbackAction(ChatConversation $conversation, Request $request)
    {
        $this->checkValidSession($conversation, $request);

        $form = $this
            ->get('form.factory')
            ->createNamedBuilder(null, 'api_chat_feedback', $conversation)
            ->getForm()
        ;

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        $this->saveConversation($conversation);

        return View::create();
    }

    /**
     * @param ChatConversation $conversation
     * @param Request          $request
     *
     * @throws BadRequestHttpException
     */
    protected function checkValidSession(ChatConversation $conversation, Request $request)
    {
        $request_session      = $this->getApiSession($request);
        $conversation_session = $conversation->getSession();

        if (!$conversation_session || $request_session->getId() !== $conversation_session->getId()) {
            throw new BadRequestHttpException('wrong_session_code');
        }
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     */
    protected function checkRequireLogin(Request $request)
    {
        $settings_resolver = $this->container->get('settings_resolver');
        $global_settings   = $settings_resolver->getGlobalSettings();
        $request_session   = $this->getApiSession($request);

        if ($global_settings->get('portal.chat.require_login') && !$request_session->getPerson()) {
            throw new BadRequestHttpException('not_authorized');
        }
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
