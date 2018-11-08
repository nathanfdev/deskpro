<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\MessengerBundle\Security\Authentication\MessengerAuthenticator;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\TechInfo;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\UserInfo;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 *
 * @Rest\Route("/user")
 */
class UserController extends AbstractMessengerController
{
    /**
     * You can use this endpoint to gather information about clients you need to obtain notifications and alerts.
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="Testing new Bundle and Kernel",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="visitorId",
     *              "requirement"="[a-zA-Z0-9\\.\\-_]+",
     *              "description"="id of the visitor to look for",
     *              "dataType"="string"
     *          }
     *      }
     * )
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return View
     */
    public function getUserInfoAction(Request $request)
    {
        $visitorId = $this->getVisitorId($request);

        /** @var EntityManager $em */
        $em                   = $this->get('doctrine.orm.default_entity_manager');
        $chatConversationRepo = $em->getRepository(ChatConversation::class);
        $chats                = $chatConversationRepo->findBy(['visitor_id' => $visitorId], ['date_created' => 'DESC'], 25);
        $actionAlertsService  = $this->get('messenger.service.action_alerts');

        $userInfo = new UserInfo($visitorId, $actionAlertsService->getLastActionAlert($visitorId));

        return View::create($this->wrap($userInfo->addChats($chats)), Response::HTTP_OK);
    }

    /**
     * You can use this endpoint to fetch latest action alerts.
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="Gathering action_alerts",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="lastActionAlert",
     *              "requirement"="\d+",
     *              "description"="id of last action alert",
     *              "dataType"="integer"
     *          }
     *      }
     * )
     * @Rest\Get("/action_alerts/{lastActionAlert}")
     *
     * @param int     $lastActionAlert
     * @param Request $request
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return View
     */
    public function getLastActionAlerts($lastActionAlert, Request $request)
    {
        $visitorId = $request->headers->get(MessengerAuthenticator::VISITOR_HEADER_NAME);

        if ($chat = $this->get('messenger.service.tech')->getLastChatByVisitorId($visitorId)) {
            $this->get('event_dispatcher')->dispatch(UserChatEvent::POLLING, new UserChatEvent($chat));
        }

        return View::create($this->get('messenger.service.action_alerts')->getActionAlerts($visitorId, $lastActionAlert), Response::HTTP_OK);
    }

    /**
     * You can use this endpoint to fetch latest action alerts.
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="Get some useful system info",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     * @Rest\Get("/info")
     */
    public function getInfo()
    {
        $techInfo            = new TechInfo($this->get('avatar_resolver'));
        $techService         = $this->get('messenger.service.tech');
        $notificationService = $this->get('deskpro.notification.service');

        $techInfo
            ->setChatDepartments($techService->getChatDepartments())
            ->setAgentsOnline($techService->getAgentsOnline())
            ->setClientsSetup($notificationService->getClientsSetup())
        ;

        return View::create($this->wrap($techInfo));
    }

    /**
     * This endpoint provide you an ability to authenticate pusher app.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about action alerts",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     parameters={
     *         {"name"="channel_name", "description"="", "dataType"="string", "required"=true},
     *         {"name"="socket_id", "description"="", "dataType"="string", "required"=true}
     *     },
     *     output="array"
     * )
     *
     * @Rest\Post("/pusher/auth")
     *
     * @param Request $request
     *
     * @return View
     */
    public function pusherAuthAction(Request $request)
    {
        $submitted = $request->request->all();
        /** @var \Pusher $pusher */
        $pusher = $this->get('deskpro.notification.pusher');
        $status = Response::HTTP_OK;
        $data   = json_decode($pusher->socket_auth($submitted['channel_name'], $submitted['socket_id']), true);

        return View::create($data, $status);
    }
}
