<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatMessageType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserChatMessagesController.
 *
 * @ApiModes("all")
 * @Rest\Route("user_chats/{conversationId}/messages")
 * @ApiDoc(target="all", section="Chats", output="Application\DeskPRO\Entity\ChatMessage")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="last_message_id", "dataType"="integer", "pattern"="\d+"},
 *          {"name"="agent_only", "dataType"="integer", "pattern"="\d+"}
 *     }
 * )
 */
class UserChatMessagesController extends CrudSubController
{
    public static $exposeOnly      = ['post', 'list', 'count'];
    public static $entity          = ChatMessage::class;
    public static $type            = ChatMessageType::class;
    public static $parentProperty  = 'conversation';
    public static $parentParameter = 'conversationId';

    /**
     * You can create new resource. Just provide well formed request.
     * Look into requirements for details.
     *
     * **We will ship resource representation as soon as it will be created.**
     *
     * @ApiDoc(
     *      description="Create a new resource",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, $this->getPermissionGroupContext($request));

        return $this->handleForm($this->instantiateEntity($request), $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'conversation' => $this->findParentOr404(),
            'person'       => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * @param ChatMessage $model
     *
     * @return ChatMessage
     */
    protected function persistModel($model)
    {
        parent::persistModel($model);

        $conversation = $model->getConversation();

        $this->get('event_dispatcher')->dispatch(UserChatEvent::SEND_MESSAGE, new UserChatEvent($conversation, $model));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $qb->andWhere($alias.'.is_user_hidden = 0');

        $lastMessageId = $request->get('last_message_id');
        if ($lastMessageId) {
            $qb->andWhere($alias.'.id > :last_message_id');
            $qb->setParameter('last_message_id', $lastMessageId);
        }
        if ($request->get('agent_only')) {
            $qb->andWhere($alias.'.is_user = 0');
        }
    }
}
