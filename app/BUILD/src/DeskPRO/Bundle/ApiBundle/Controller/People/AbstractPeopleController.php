<?php

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
