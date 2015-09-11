<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/
/**
 * DeskPRO
 *
 * @package DeskPRO
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\AgentChat;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController as BaseController;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\AgentChat\History;
use DeskPRO\Bundle\AppBundle\AgentChat\Messenger;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatsController extends BaseController
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
     * @param Request $request
     * @Annotations\Get("/agent_chats", name="agent_chats_list")
     * @return View
     */
    public function cgetAction(Request $request)
    {
        /** @var History $searchService */
        $searchService = $this->get('deskpro.agentchat.history');
        $user = $this->getUser();
        $requestParams = $request->query->all();

        if(isset($requestParams['search'])) {
            $searchString = $requestParams['search'];
        } else {
            $searchString = '';
        }

        $chats = $searchService->searchAllChats($user, $searchString);

        return View::create(
            $this->DataSerialize($chats),
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
     * @param integer $id
     * @return View
     * @throws NotFoundHttpException
     */
    public function getAction($id)
    {
        return View::create(
            $this->DataSerialize($this->getChat($id)),
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
     * @Annotations\Post("/agent_chats", name="agent_chats_add_chat")
     *
     * @param Request $request
     * @return View
     * @throws InvalidFormException
     * @throws BadRequestHttpException
     * @todo looks like we have to implement some custom logic here, cause we have to handle agents/teams/departments
     * @todo manually, and just do it with form is too complicated, maybe we can auto generate form for every AgentChat
     * @todo participant entity, and then a big one for this participants collection?
     */
    public function postAction(Request $request)
    {
        $status = Response::HTTP_CREATED;
        $submitted = $request->request->all();
        if (!count($submitted)) {
            throw new BadRequestHttpException();
        }
        $participants = array();
        if(isset($submitted['agents'])) {
            foreach($submitted['agents'] as $agentId) {
                $participant = $this->em()
                    ->getRepository('DeskPRO:Person')
                    ->find((int)$agentId);
                if($participant) {
                    $participants[] = $participant;
                    $participant = null;
                }
            }
        }
        if(isset($submitted['teams'])) {
            foreach($submitted['teams'] as $teamId) {
                $participant = $this->em()
                    ->getRepository('DeskPRO:AgentTeam')
                    ->find((int)$teamId);
                if($participant) {
                    $participants[] = $participant;
                    $participant = null;
                }
            }
        }
        if(isset($submitted['departments'])) {
            foreach($submitted['departments'] as $departmentId) {
                $participant = $this->em()
                    ->getRepository('DeskPRO:AgentTeam')
                    ->find((int)$departmentId);
                if($participant) {
                    $participants[] = $participant;
                    $participant = null;
                }
            }
        }
        if(!count($participants)) {
            throw new BadRequestHttpException();
        }
        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        $user = $this->getUser();
        $chat = $messenger->createChat($user, $participants);
        $this->em()->persist($chat);
        $this->em()->flush();
        return View::create(
            $this->dataSerialize($chat),
            $status,
            array(
                'Location' => $this->generateUrl('agent_chats_view_chat', array('id' => $chat->getId()))
            )
        );
    }


    /**
     * @param $id
     * @param Request $request
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @return View
     * @Annotations\Get("/agent_chats/{id}/messages", name="agent_chats_get_messages")
     */
    public function getMessagesAction($id, Request $request)
    {
        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        $user = $this->getUser();
        $chat = $this->getChat($id);

        if(!$user || !$messenger->isPersonInvolvedInChat($user, $chat)) {
            throw new AccessDeniedHttpException();
        }

        $searchString = $request->query->getAlnum('search', '');
        $orderBy = $request->query->get('order', 'date_created');
        $page = $request->query->getInt('page', 1);
        /** @var History $searchService */
        $searchService = $this->get('deskpro.agentchat.history');
        $messages = $searchService->searchInChat($chat, $searchString, $orderBy);

        $pager = new Pagerfanta(new ArrayAdapter($messages));
        $pager->setCurrentPage($page);
        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @param $id
     * @param Request $request
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws InvalidFormException
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
        $user = $this->getUser();
        $chat = $this->getChat($id);

        if(!$user || !$messenger->isPersonInvolvedInChat($user, $chat)) {
            throw new AccessDeniedHttpException();
        }

        $data = $form->getData();
        $message = $messenger->addMessage($chat, $user, $data['message']);
        return View::create(
            $this->dataSerialize($message),
            Response::HTTP_CREATED
        );
    }

    /**
     * @param $id
     * @return AgentChat|null
     * @throws NotFoundHttpException;
     */
    private function getChat($id)
    {
        /** @var Messenger $messenger */
        $messenger = $this->get('deskpro.agentchat.messenger');
        if(!$chat = $messenger->getChat($id))
        {
            throw new NotFoundHttpException();
        }
        return $chat;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    private function em()
    {
        return $this->getDoctrine()->getManager();
    }
}