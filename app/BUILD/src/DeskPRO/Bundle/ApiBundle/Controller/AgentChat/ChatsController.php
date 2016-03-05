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
use DeskPRO\Bundle\AppBundle\AgentChat\History;
use DeskPRO\Bundle\AppBundle\AgentChat\Messenger;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ChatsController.
 *
 * @ApiModes("all")
 */
class ChatsController extends AbstractController
{
    /**
     * @ApiDoc(
     *      section = "Agent`s chat",
     *      resourceDescription="Operations about agent chats",
     *      description="get agent`s chats collection",
     *      filters={
     *          {
     *              "name"="search",
     *              "dataType"="string"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if response was succesful",
     *      },
     *      output={
     *          "class"="array<DeskPRO\Bundle\AppBundle\Entity\AgentChat>",
     *      }
     * )
     *
     * @param Request $request
     * @Annotations\Get("/agent_chats", name="agent_chats_list")
     * @FOS\View(serializerEnableMaxDepthChecks=true)
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        /** @var History $searchService */
        $searchService = $this->get('deskpro.agentchat.history');
        $user          = $this->getUser();
        $requestParams = $request->query->all();

        $chats = $searchService->findChats($user, ['id' => 'ASC']);

        if (isset($requestParams['search'])) {
            $searchString = $requestParams['search'];
            $chats        = $searchService->searchAllChats($chats, $searchString);
        }

        return View::create(
            $this->dataSerialize($chats),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      section="Agent`s chat",
     *      resourceDescription="Operations about agent chats",
     *      description="get recently participating agent`s chats collection",
     *      filters={
     *          {"name"="search", "dataType"="string"}
     *      },
     *      statusCodes={
     *          200="Success",
     *      },
     *      output="array<DeskPRO\Bundle\AppBundle\Entity\AgentChat>"
     * )
     *
     * @param Request $request
     * @Annotations\Get("/agent_chats/recent", name="agent_chats_list_recent")
     * @FOS\View(serializerEnableMaxDepthChecks=true)
     *
     * @return View
     */
    public function recentAction(Request $request)
    {
        /** @var History $searchService */
        $searchService = $this->get('deskpro.agentchat.history');
        $user          = $this->getUser();
        $requestParams = $request->query->all();

        $chats = $searchService->findChats($user);

        if (isset($requestParams['search'])) {
            $searchString = $requestParams['search'];
            $chats        = $searchService->searchAllChats($chats, $searchString);
        }

        return View::create(
            $this->dataSerialize($chats),
            Response::HTTP_OK
        );
    }

    /**
     * Get an agent`s chat with given id.
     *
     * @ApiDoc(
     *      section = "Agent`s chat",
     *      resourceDescription="Operations about agent chats",
     *      description="get an agent`s chat",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of chat",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success when chat was found",
     *          404="Returned when chat was not found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\AgentChat"
     * )
     *
     * @Annotations\Get("/agent_chats/{id}", name="agent_chats_view_chat")
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function getAction($id)
    {
        return View::create(
            $this->dataSerialize($this->getChat($id)),
            Response::HTTP_OK
        );
    }

    /**
     * This endpoint gives an ability to start chat with some person, team, department or with everyone in helpdesk.
     *
     * @ApiDoc(
     *     section = "Agent`s chat",
     *     resourceDescription="Operations about agent chats",
     *     description = "create an agent`s chat",
     *     requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="an entity identificator"
     *      },
     *      {
     *          "name"="type",
     *          "dataType"="integer",
     *          "requirement"="(agent|team|department|everyone)",
     *          "description"="an entity type to start chat with"
     *      }
     *     },
     *     statusCodes = {
     *       201 = "Chat was created",
     *       302 = "We found already started chat with given parameters",
     *       400 = "Couldn't start chat with given parameters"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\AgentChat"
     * )
     * @Annotations\Post("/agent_chats/start", name="agent_chats_add_chat_with_agent")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     * @throws BadRequestHttpException
     *
     * @return View
     */
    public function startAction(Request $request)
    {
        $status = Response::HTTP_CREATED;

        $form = $this->submitForm('api_agent_chat_start_chat', $request->request);
        if (!$form->isValid()) {
            $errors = $this->createFormErrorsData($form);
            $status = Response::HTTP_BAD_REQUEST;

            return View::create(
                $this->createErrorRepresentation($status, $status, "Couldn't start chat", $errors),
                $status
            );
        }

        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');

        if (!$entity = $messenger->findParticipant($form->get('type')->getData(), $form->get('id')->getData())) {
            $status = Response::HTTP_BAD_REQUEST;

            return View::create(
                $this->createErrorRepresentation($status, $status, "Couldn't start chat", ['Target not found!']),
                $status
            );
        }

        $user = $this->getUser();

        if ($chat = $messenger->findChat($user, $entity)) {
            $status = Response::HTTP_FOUND;
        } else {
            $chat = $messenger->startChat($user, $entity);
            $this->em()->persist($chat);
            $this->em()->flush();
            $this->em()->clear();
            $chat = $messenger->getChat($chat->getId());
        }

        return View::create(
            $this->dataSerialize($chat),
            $status,
            [
                'Location' => $this->generateUrl('agent_chats_view_chat', ['id' => $chat->getId()]),
            ]
        );
    }
}
