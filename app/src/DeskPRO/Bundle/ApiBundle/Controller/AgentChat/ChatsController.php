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
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\Chatable;
use DeskPRO\Bundle\AppBundle\AgentChat\Messenger;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatsController extends AbstractController
{
    /**
     * @ApiDoc(
     *      description="get agent-chat collection",
     *      filters={
     *          {"name"="search", "dataType"="string"}
     *      },
     *      statusCodes={
     *          200="Success",
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\AgentChat"
     * )
     *
     * @param Request $request
     * @Annotations\Get("/agent_chats", name="agent_chats_list")
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
     *      description="get agent-chat collection",
     *      filters={
     *          {"name"="search", "dataType"="string"}
     *      },
     *      statusCodes={
     *          200="Success",
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\AgentChat"
     * )
     *
     * @param Request $request
     * @Annotations\Get("/agent_chats/recent", name="agent_chats_list_recent")
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
     * @ApiDoc(
     *      description="get an agent-chat",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the chat",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
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
     * @ApiDoc(
     *      description="create an agent-chat",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\AgentChat"
     * )
     * @ Annotations\Post("/agent_chats", name="agent_chats_add_chat")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     * @throws BadRequestHttpException
     *
     * @return View
     *
     * @deprecated
     *
     * @todo looks like we have to implement some custom logic here, cause we have to handle agents/teams/departments
     * @todo manually, and just do it with form is too complicated, maybe we can auto generate form for every AgentChat
     * @todo participant entity, and then a big one for this participants collection?
     */
    public function postAction(Request $request)
    {
        $status    = Response::HTTP_CREATED;
        $submitted = $request->request->all();
        if (!count($submitted)) {
            throw new BadRequestHttpException();
        }
        $participants = array();
        if (isset($submitted['agents'])) {
            foreach ($submitted['agents'] as $agentId) {
                $participant = $this->em()
                    ->getRepository('DeskPRO:Person')
                    ->find((int) $agentId);
                if ($participant) {
                    $participants[] = $participant;
                    $participant    = null;
                }
            }
        }
        if (isset($submitted['teams'])) {
            foreach ($submitted['teams'] as $teamId) {
                $participant = $this->em()
                    ->getRepository('DeskPRO:AgentTeam')
                    ->find((int) $teamId);
                if ($participant) {
                    $participants[] = $participant;
                    $participant    = null;
                }
            }
        }
        if (isset($submitted['departments'])) {
            foreach ($submitted['departments'] as $departmentId) {
                $participant = $this->em()
                    ->getRepository('DeskPRO:Department')
                    ->find((int) $departmentId);
                if ($participant) {
                    $participants[] = $participant;
                    $participant    = null;
                }
            }
        }
        if (!count($participants)) {
            throw new BadRequestHttpException();
        }
        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        $user      = $this->getUser();
        $chat      = $messenger->createChat(array_merge($user, $participants), Chatable::PARTICIPANT_TYPE_GROUP);
        $this->em()->persist($chat);
        $this->em()->flush();

        return View::create(
            $this->dataSerialize($chat),
            $status,
            array(
                'Location' => $this->generateUrl('agent_chats_view_chat', array('id' => $chat->getId())),
            )
        );
    }

    /**
     * This is just a stub to make possible start or find tet-a-tet chats.
     * In current UI implementation there is no way to know about chats id - only agent pictures,
     * department pictures and teams pictures. So the main point is that you can start chat with agent only.
     *
     * @ApiDoc(
     *      description="create an agent-chat with agent",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\AgentChat"
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

        $submitted = $request->request->all();
        if (!isset($submitted['type']) || !isset($submitted['id'])) {
            throw new BadRequestHttpException();
        }

        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');

        if (!$entity = $messenger->findParticipant($submitted['type'], $submitted['id'])) {
            throw new BadRequestHttpException();
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
            array(
                'Location' => $this->generateUrl('agent_chats_view_chat', array('id' => $chat->getId())),
            )
        );
    }
}
