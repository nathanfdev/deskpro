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
        $agentIds   = $connection->fetchAllCol(<<<SQL
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
