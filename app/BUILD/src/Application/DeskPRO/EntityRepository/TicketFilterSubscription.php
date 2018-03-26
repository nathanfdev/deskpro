<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person as PersonEntity;

class TicketFilterSubscription extends AbstractEntityRepository
{
    public function getForAgent(PersonEntity $person)
    {
        $results = $this->getEntityManager()->createQuery('
            SELECT s
            FROM DeskPRO:TicketFilterSubscription s
            LEFT JOIN s.filter f
            WHERE s.person = ?1
        ')->execute([1 => $person]);

        $ret = [];

        foreach ($results as $s) {
            $ret[$s->filter->id] = $s;
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getSubscribedActiveAgentIds()
    {
        /** @var Connection $connection */
        $connection = $this->getEntityManager()->getConnection();
        $agentIds   = $connection->fetchAllCol(<<<'SQL'
            SELECT DISTINCT s.person_id
            FROM ticket_filter_subscriptions s
            JOIN people p ON p.id = s.person_id
            WHERE p.is_agent = 1 AND p.is_disabled = 0 AND p.is_deleted = 0
SQL
        );

        return array_map('intval', $agentIds);
    }

    /**
     * Return an array of subscription info for all agents in $people, optionally only for $filters.
     *
     * Returned array structure:
     * <code>
     * array(
     *     // agend id => TicketFilterSubscription[]
     *     123 => array(
     *         14 => TicketFilterSubscription['email_new', ...],
     *         // filter id => TicketFilterSubscription
     *     )
     * )
     * </code>
     *
     * @param array $peopleIds
     * @param array $filtersIds
     *
     * @return array
     */
    public function getForAgents(array $peopleIds, array $filtersIds = null)
    {
        if (!$peopleIds) {
            return [];
        }

        $qb = $this->_em->getConnection()->createQueryBuilder();
        $qb
            ->select('*')
            ->from('ticket_filter_subscriptions', 's')
            ->where('s.person_id IN (:people_ids)')
            ->setParameter('people_ids', $peopleIds, Connection::PARAM_INT_ARRAY)
        ;

        if ($filtersIds) {
            $qb->andWhere('s.filter_id IN (:filter_ids)');
            $qb->setParameter('filter_ids', $filtersIds, Connection::PARAM_INT_ARRAY);
        }

        $results = $qb->execute()->fetchAll();

        $ret = [];
        foreach ($results as $s) {
            $personId = $s['person_id'];
            $filterId = $s['filter_id'];

            if (!isset($ret[$personId])) {
                $ret[$personId] = [];
            }

            $ret[$personId][$filterId] = $s;
        }

        return $ret;
    }
}
