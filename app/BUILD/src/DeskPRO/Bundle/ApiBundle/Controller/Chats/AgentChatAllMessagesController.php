<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\ApiBundle\Traits\AgentChatFiltersTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AgentChatsMessagesCountController.
 *
 * @ApiModes("all")
 * @Rest\Route("/agent_chats/messages")
 * @ApiUnstable()
 * @ApiDoc(target="all", section="Chats", output="DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage")
 * @Feature("agent_chat")
 */
class AgentChatAllMessagesController extends CrudController
{
    use AgentChatFiltersTrait;

    public static $entity     = AgentChatMessage::class;
    public static $exposeOnly = ['count'];

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->leftJoin("$alias.chat", 'chat');
        $this->applyParticipantFilters($qb, 'chat');

        $search = $request->get('search');
        if ($search) {
            $qb->andWhere("$alias.message LIKE :search");
            $qb->setParameter('search', "%$search%");
        }

        if ($request->get('not_my')) {
            $qb->andWhere("$alias.person != :me");
            $qb->setParameter('me', $this->getUser());
        }

        ListHelper::applyInListFilter(new RequestQueryContext($qb, $alias, $request), 'status');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        if ($groupBy === 'chat') {
            $qb
                ->addSelect('chat.id as group_name')
                ->addSelect('chat.id as title')
                ->groupBy('group_name')
            ;
        }
    }
}
