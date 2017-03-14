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
 * @Feature(id="agent_chat", beta=true)
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
