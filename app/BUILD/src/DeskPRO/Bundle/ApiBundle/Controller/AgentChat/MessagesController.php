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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
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
 */
class MessagesController extends AbstractController
{
    /**
     * Get messages for chat with given id.
     *
     * @ApiDoc(
     *      section="Agent`s chat",
     *      resourceDescription="Operations about agent`s chat messages",
     *      description="get agent`s chat`s messages",
     *      filters={
     *          {"name"="search", "dataType"="string"},
     *          {"name"="order", "dataType"="string", "pattern"="date_created"}
     *      },
     *      statusCodes={
     *          200="Returned if success",
     *          403="Returned if user has no access to current chat",
     *          404="Returned if there is no chat with given id",
     *          400="Returned if filters was wrong"
     *      },
     *      output="array<DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage>"
     * )
     *
     * @Annotations\Get("/agent_chats/{id}/messages", name="agent_chats_get_messages")
     * @Annotations\View(serializerEnableMaxDepthChecks=true)
     * @ApiModes("all")
     *
     * @param $id
     * @param Request $request
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return View
     */
    public function getMessagesAction($id, Request $request)
    {
        $chat = $this->getChat($id);

        $form = $this->submitForm('api_agent_chat_search_messages', $request->query);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $search_service = $this->get('deskpro.agentchat.history');
        $messages       = $search_service->searchInChat($chat, $form->get('search')->getData(), $form->get('order')->getData());

        $pager = new Pagerfanta(new ArrayAdapter($messages));
        $pager->setMaxPerPage(9);
        $pager->setCurrentPage($request->query->getInt('page', 1));

        return View::create(
            new ApiWrapper($pager),
            Response::HTTP_OK
        );
    }

    /**
     * Create a new message in chat with given id.
     *
     * @ApiDoc(
     *      section="Agent`s chat",
     *      resourceDescription="Operations about agent`s chat messages",
     *      description="post a message",
     *      requirements={
     *          {
     *              "name"="chat",
     *              "requirement"="\d+",
     *              "dataType"="integer",
     *              "description"="ID of chat where to add a message (in uri)"
     *          },
     *          {
     *              "name"="message",
     *              "requirement"=".*",
     *              "dataType"="string",
     *              "description"="any string to add as message"
     *          },
     *          {
     *              "name"="uuid",
     *              "requirement"="[0-9a-Z-]+",
     *              "dataType"="string",
     *              "description"="an unique identificator of message"
     *          },
     *      },
     *      statusCodes={
     *          201="Returned when message was successfully added",
     *          400={
     *              "Returned if message was empty",
     *              "Returned if uuid was empty or wrong formatted"
     *          },
     *          403="Returned if person is not participating in chat"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage"
     * )
     *
     * @param AgentChat $chat
     * @param Request   $request
     *
     * @return View
     * @Annotations\Post("/agent_chats/{chat}/messages", name="agent_chats_add_chat_message")
     */
    public function postMessagesAction(AgentChat $chat, Request $request)
    {
        $user = $this->getUser();
        if (!$user || !$this->get('deskpro.agentchat.messenger')->isPersonInvolvedInChat($user, $chat)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this
            ->get('form.factory')
            ->createNamedBuilder(null, 'api_agent_chat_message', null, [
                'person' => $user,
                'chat'   => $chat,
            ])
            ->getForm()
        ;

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->em()->persist($chat);
        $this->em()->flush();

        $message = $form->getData();

        $this->container->get('event_dispatcher')->dispatch(
            NewMessageEvent::EVENT_NAME,
            new NewMessageEvent($message->getId())
        );

        return View::create($this->dataSerialize($message), Response::HTTP_CREATED);
    }

    /**
     * Get messages count.
     *
     * @ApiDoc(
     *      section="Agent`s chat",
     *      resourceDescription="Operations about agent`s chat messages",
     *      description="get messages count",
     *      statusCodes={
     *          200="Returned if success"
     *      },
     *      output={
     *          "\d+" = {
     *              "chat_id" = "\d+",
     *              "cnt" = "\d+",
     *              "chat" = "DeskPRO\Bundle\AppBundle\Entity\AgentChat"
     *          }
     *      }
     * )
     *
     * @return View
     * @Annotations\Get("/agent_chats/messages/count", name="agent_chats_messages_count")
     */
    public function countsAction()
    {
        $search_service = $this->get('deskpro.agentchat.history');
        $helper_service = $this->get('deskpro.agentchat.helper');
        $count          = $search_service->countMessages($this->getUser());
        $data           = $helper_service->createCountResponse($count);

        return View::create(new ApiWrapper($data), Response::HTTP_OK);
    }

    /**
     * Mark message with given id as sent/read.
     *
     * @ApiDoc(
     *      section="Agent`s chat",
     *      resourceDescription="Operations about agent`s chat messages",
     *      description="mark message as sent/read",
     *      requirements={
     *          {
     *              "name"="ids",
     *              "requirement"="[\d+]",
     *              "dataType"="integer[]",
     *              "description"="Array of ids to update with given status"
     *          },
     *          {
     *              "name"="status",
     *              "requirement"="1|2",
     *              "dataType"="integer",
     *              "description"="Read status. 1 => sent, 2 => read"
     *          },
     *      },
     *      statusCodes={
     *          204="Returned if success",
     *          400={
     *              "Returned if given status was wrong",
     *              "Returned if ids list was wrong formed"
     *          }
     *      },
     * )
     *
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
            throw new InvalidFormException($form);
        }

        $messenger = $this->get('deskpro.agentchat.messenger');
        $messenger->markMessages(
            $form->get('ids')->getData(),
            $form->get('status')->getData(),
            $this->getUser()
        );

        return new Response('', $status);
    }
}
