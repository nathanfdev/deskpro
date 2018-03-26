<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use Application\DeskPRO\Entity\PersonPref;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\AgentChatFiltersTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatRepository;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\AgentChat\AgentChatType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AgentChatsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/agent_chats")
 * @ApiUnstable()
 * @Feature("agent_chat")
 * @ApiDoc(target="all", section="Chats", output="DeskPRO\Bundle\AppBundle\Entity\AgentChat")
 * @ApiDoc(target="getAction", documentation="Retrieves an agent chat with provided id")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\AgentChat\AgentChatType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\AgentChat",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class AgentChatsController extends CrudController
{
    use AgentChatFiltersTrait;

    public static $entity      = AgentChat::class;
    public static $type        = AgentChatType::class;
    public static $listOrder   = 'asc';
    public static $sortOptions = [
        'date_last_message' => 'date_last_message',
    ];

    /**
     * This endpoint gives an ability to start chat with some person, team, department or with everyone in helpdesk.
     *
     * @ApiDoc(
     *     section = "Chats",
     *     resourceDescription="Operations about agent chats",
     *     description = "create an agent`s chat",
     *     requirements={
     *      {
     *          "name"="participant",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="an entity identifier"
     *      },
     *      {
     *          "name"="type",
     *          "dataType"="integer",
     *          "requirement"="(agent|team|department|everyone|group)",
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
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, $this->getPermissionGroupContext($request));

        $form = $this->createForm(static::$type, $this->instantiateEntity($request), ['person' => $this->getUser()]);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $entity = $form->getData();
        $status = $entity->getId() ? Response::HTTP_FOUND : Response::HTTP_CREATED;

        $view = View::create($this->wrap($this->persistModel($entity)), $status);
        $view->setLocation($this->getLocationUrl($entity, $request));

        return $view;
    }

    /**
     * This endpoint gives an ability to start chat with some person, team, department or with everyone in helpdesk.
     *
     * @ApiDoc(
     *     section = "Chats",
     *     resourceDescription="Operations about agent chats",
     *     description = "update an agent chat",
     *     requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="a chat id"
     *      },
     *      {
     *          "name"="participant",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="an entity identifier"
     *      },
     *      {
     *          "name"="type",
     *          "dataType"="integer",
     *          "requirement"="(agent|team|department|everyone|group)",
     *          "description"="an entity type to start chat with"
     *      }
     *     },
     *     statusCodes = {
     *       204 = "Chat was updated"
     *     }
     * )
     * @Rest\Put("/{id}")
     *
     * @param int     $id
     * @param Request $request
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function putAction($id, Request $request)
    {
        /** @var AgentChat $chat */
        $chat = $this->findEntity($id, $request);
        if ($chat->getAdmin() && $chat->getAdmin() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied'); // temporary stub I guess
        }
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, $this->getPermissionGroupEntityContext($id, $request));

        return $this->handleForm($this->findEntity($id, $request), $request, ['person' => $this->getUser()]);
    }

    /**
     * This endpoint gives an ability to start chat with some person, team, department or with everyone in helpdesk.
     *
     * @ApiDoc(
     *     section = "Chats",
     *     resourceDescription="Operations about agent chats",
     *     description = "get agent chat groups",
     *     output="<DeskPRO\Bundle\AppBundle\Entity\AgentChat>"
     * )
     * @Rest\Get("/groups")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function getGroupChatsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        /** @var AgentChatRepository $repo */
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository(AgentChat::class);

        return View::create($this->wrap($repo->findGroupChats($this->getUser())), Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $this->applyParticipantFilters($qb, $alias);

        $search = $request->get('search');
        if ($search) {
            $qb->leftJoin("$alias.messages", 'messages');
            $qb->andWhere('messages.message LIKE :search');
            $qb->setParameter('search', "%$search%");
        }
    }

    /**
     * This endpoint gives an ability to start chat with some person, team, department or with everyone in helpdesk.
     *
     * @ApiDoc(
     *     section = "Chats",
     *     resourceDescription="Operations about agent chats",
     *     description = "delete group",
     *     requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="a chat id"
     *      }
     *     },
     *     statusCodes = {
     *       204 = "Chat was deleted",
     *       403 = "You are not admin or chat is not group chat"
     *     }
     * )
     * @Rest\Delete("/{id}/delete")
     *
     * @param int $id
     *
     * @return View
     */
    public function deleteGroupAction($id)
    {
        /** @var AgentChat $chat */
        $chat = $this->findOr404(static::$entity, $id);
        if ($chat->getAdmin() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not admin for this chat');
        }
        if ($chat->getType() !== AgentChat::TYPE_GROUP) {
            throw $this->createAccessDeniedException(sprintf('You are allowed to delete %s chat', $chat->getType()));
        }
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $entityManager->remove($chat);
        $entityManager->flush();

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * This endpoint gives an ability to start chat with some person, team, department or with everyone in helpdesk.
     *
     * @ApiDoc(
     *     section = "Chats",
     *     resourceDescription="Operations about agent chats",
     *     description = "leave group",
     *     requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="a chat id"
     *      }
     *     },
     *     statusCodes = {
     *       204 = "Chat was deleted",
     *       403 = "You are admin or chat is not group chat"
     *     }
     * )
     * @Rest\Delete("/{id}/leave")
     *
     * @param int $id
     *
     * @return View
     */
    public function leaveGroupAction($id)
    {
        /** @var AgentChat $chat */
        $chat = $this->findOr404(static::$entity, $id);
        if ($chat->getAdmin() === $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to leave chat, only delete it');
        }
        if ($chat->getType() !== AgentChat::TYPE_GROUP) {
            throw $this->createAccessDeniedException(sprintf('You are allowed to delete %s chat', $chat->getType()));
        }
        $chat->removeParticipant($this->getUser());
        $this->get('doctrine.orm.default_entity_manager')->flush();

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/chats_order")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function saveChatsOrderAction(Request $request)
    {
        $chatsOrder = $request->request->get('chats_order');

        $pref = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(PersonPref::class)
            ->findOneBy(['name' => 'agent.ui.im.chats_order', 'person' => $this->getUser()]);

        if (!$pref) {
            $pref = new PersonPref();
            $pref
                ->setPerson($this->getUser())
                ->setName('agent.ui.im.chats_order');
        }
        $i = 1;
        asort($chatsOrder);
        foreach ($chatsOrder as &$preference) {
            $preference = (int) $i;
            ++$i;
        }
        $pref->setValueArray($chatsOrder);
        $em = $this->get('doctrine.orm.default_entity_manager');
        $em->persist($pref);
        $em->flush();
    }

    /**
     * @Rest\Put("/{id}/hide")
     *
     * @param int $id
     *
     * @return View
     */
    public function hideChatAction($id)
    {
        $this->setChatPinned($id, false);
    }

    /**
     * @Rest\Put("/{id}/reveal")
     *
     * @param int $id
     *
     * @return View
     */
    public function revealChatAction($id)
    {
        $this->setChatPinned($id, true);
    }

    /**
     * @param int  $id
     * @param bool $pinned
     */
    private function setChatPinned($id, $pinned = false)
    {
        /** @var AgentChat $chat */
        $chat = $this->findOr404(static::$entity, $id);
        $em   = $this->get('doctrine.orm.default_entity_manager');
        $chat->setPinned($pinned);
        $em->persist($chat);
        $em->flush();
    }
}
