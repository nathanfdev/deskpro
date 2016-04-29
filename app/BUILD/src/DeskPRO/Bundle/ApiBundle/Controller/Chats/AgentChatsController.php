<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\AgentChatFiltersTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
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
 * @ApiDoc(target="all", section="Chats", output="DeskPRO\Bundle\AppBundle\Entity\AgentChat")
 * @ApiUnstable()
 */
class AgentChatsController extends CrudController
{
    use AgentChatFiltersTrait;

    public static $exposeOnly  = ['get', 'list', 'count', 'post', 'delete'];
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
        $form->submit($this->getRequestContent($request));
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
}
