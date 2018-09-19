<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\UserInfo;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 *
 * @Rest\Route("/user")
 */
class UserController extends BaseController
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
     * @Rest\Get("/{visitorId}")
     *
     * @param string $visitorId
     *
     * @return View
     */
    public function loadUserInfoAction($visitorId)
    {
        /** @var EntityManager $em */
        $em                   = $this->get('doctrine.orm.default_entity_manager');
        $chatConversationRepo = $em->getRepository(ChatConversation::class);
        $chats                = $chatConversationRepo->findBy(['visitor_id' => $visitorId], ['date_created' => 'DESC'], 25);
        $userInfo             = new UserInfo($visitorId);

        return View::create($this->wrap($userInfo->addChats($chats)), Response::HTTP_OK);
    }
}
