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

        $search_string = $request->query->get('search', '');
        $orderBy       = $request->query->get('order', 'date_created');
        $page          = $request->query->getInt('page', 1);
        /** @var History $search_service */
        $search_service = $this->get('deskpro.agentchat.history');
        $messages       = $search_service->searchInChat($chat, $search_string, $orderBy);

        $pager = new Pagerfanta(new ArrayAdapter($messages));
        $pager->setMaxPerPage(9);
        $pager->setCurrentPage($page);

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
        $form = $this->createFormBuilder(array('message' => null))
            ->add('message', 'text')
            ->getForm();
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }
        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        $user      = $this->getUser();
        $chat      = $this->getChat($id);

        if (!$user || !$messenger->isPersonInvolvedInChat($user, $chat)) {
            throw new AccessDeniedHttpException();
        }

        $data    = $form->getData();
        $message = $messenger->addMessage($chat, $user, $data['message']);
        // TODO just a stub maybe
        $this->container->get('event_dispatcher')->dispatch(
            NewMessageEvent::EVENT_NAME,
            new NewMessageEvent($message->getId())
        );

        return View::create(
            $this->dataSerialize($message),
            Response::HTTP_CREATED
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
     * @Annotations\Patch("/agent_chats/messages/mark", name="agent_chats_messages_mark")
     */
    public function markAction(Request $request)
    {
        $status = Response::HTTP_ACCEPTED;
        $ids    = $request->request->get('ids');
        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        $messenger->markAsRead($ids, $this->getUser());

        return View::create(
            $this->createRepresentation([]),
            $status
        );
    }
}
