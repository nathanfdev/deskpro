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

namespace DeskPRO\Bundle\ApiBundle\Controller\AgentChat;

use DeskPRO\Bundle\AppBundle\AgentChat\History;
use DeskPRO\Bundle\AppBundle\AgentChat\Messenger;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class MessagesController.
 *
 * @ApiModes("all")
 * @ApiTags("agent.agent_chat.messages")
 */
class MessagesController extends AbstractController
{
    /**
     * @param $id
     * @param Request $request
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return View
     * @Annotations\Get("/agent_chats/{id}/messages", name="agent_chats_get_messages")
     * @ApiModes("standard")
     * @ApiTags("agent.agent_chat.messages.get_messages")
     */
    public function getMessagesAction($id, Request $request)
    {
        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        $user      = $this->getUser();
        $chat      = $this->getChat($id);

        if (!$user || !$messenger->isPersonInvolvedInChat($user, $chat)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->submitForm('api_agent_chat_search_messages', $request->query);
        if (!$form->isValid()) {
            $errors = $this->createFormErrorsData($form);
            $status = Response::HTTP_BAD_REQUEST;

            return View::create(
                $this->createErrorRepresentation(
                    $status,
                    'request_error',
                    "Couldn't process your request",
                    $errors
                ),
                $status
            );
        }

        /** @var History $search_service */
        $search_service = $this->get('deskpro.agentchat.history');
        $messages       = $search_service->searchInChat($chat, $form->get('search')->getData(), $form->get('order')->getData());

        $pager = new Pagerfanta(new ArrayAdapter($messages));
        $pager->setMaxPerPage(9);
        $pager->setCurrentPage($form->get('page')->getData());

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @param $id
     * @param Request $request
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws InvalidFormException
     *
     * @return View
     * @Annotations\Post("/agent_chats/{id}/messages", name="agent_chats_add_chat_message")
     */
    public function postMessagesAction($id, Request $request)
    {
        $status = Response::HTTP_CREATED;

        $form = $this->submitForm('api_agent_chat_message', $request->request);
        if (!$form->isValid()) {
            $errors = $this->createFormErrorsData($form);
            $status = Response::HTTP_BAD_REQUEST;

            return View::create(
                $this->createErrorRepresentation(
                    $status,
                    'request_error',
                    "Couldn't create message",
                    $errors
                ),
                $status
            );
        }

        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        $user      = $this->getUser();
        $chat      = $this->getChat($id);

        if (!$user || !$messenger->isPersonInvolvedInChat($user, $chat)) {
            throw new AccessDeniedHttpException();
        }

        $message = $messenger->addMessage(
            $chat,
            $user,
            $form->get('message')->getData(),
            $form->get('uuid')->getData()
        );

        $this->container->get('event_dispatcher')->dispatch(
            NewMessageEvent::EVENT_NAME,
            new NewMessageEvent($message->getId())
        );

        return View::create(
            $this->dataSerialize($message),
            $status
        );
    }

    /**
     * @return View
     * @Annotations\Get("/agent_chats/messages/count", name="agent_chats_messages_count")
     */
    public function countsAction()
    {
        /** @var History $search_service */
        /* @var Messenger $messenger */

        $search_service = $this->get('deskpro.agentchat.history');
        $helper_service = $this->get('deskpro.agentchat.helper');
        $count          = $search_service->countMessages($this->getUser());
        $data           = $helper_service->createCountResponse($count);

        return View::create(
            $this->createRepresentation($data),
            Response::HTTP_OK
        );
    }

    /**
     * @param Request $request
     *
     * @return View
     * @Annotations\Put("/agent_chats/messages/mark", name="agent_chats_messages_mark")
     */
    public function markAction(Request $request)
    {
        $status = Response::HTTP_ACCEPTED;
        $form   = $this->submitForm('api_agent_chat_mark_message', $request->request);
        if (!$form->isValid()) {
            $status = Response::HTTP_BAD_REQUEST;
            $errors = $this->createFormErrorsData($form);

            return View::create(
                $this->createErrorRepresentation(
                    $status,
                    'form_error',
                    "Couldn't mark messages",
                    $errors
                ),
                $status
            );
        }

        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        $messenger->markMessages(
            $form->get('ids')->getData(),
            $form->get('status')->getData(),
            $this->getUser()
        );

        return View::create(
            $this->createRepresentation([]),
            $status
        );
    }
}
