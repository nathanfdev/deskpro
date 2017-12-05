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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractPeopleController.
 */
abstract class AbstractPeopleController extends CrudController
{
    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $isDeleted = $request->get('is_deleted', 0);
        if ((int) $isDeleted !== -1) {
            $qb->andWhere("$alias.is_deleted = :is_deleted");
            $qb->setParameter('is_deleted', (bool) $isDeleted);
        }

        $isOnline = $request->get('online', -1);
        if ((int) $isOnline !== -1) {
            $onlineIds = $this->getContainer()->getAgentData()->getOnlineAgentIds();
            $op        = $isOnline ? 'IN' : 'NOT IN';

            $qb->andWhere("$alias.id $op (:online_ids)");
            $qb->setParameter('online_ids', $onlineIds);
        }

        $isOnlineForChat = $request->get('online_for_chat', -1);
        if ((int) $isOnlineForChat !== -1) {
            /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
            $personRepo       = $this->getManager()->getRepository(Person::class);
            $onlineForChatIds = $personRepo->getActiveAgentIdsForUserChat();
            $op               = $isOnlineForChat ? 'IN' : 'NOT IN';

            $qb->andWhere("$alias.id $op (:online_for_chat_ids)");
            $qb->setParameter('online_for_chat_ids', $onlineForChatIds);
        }
    }
}
