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

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class SnippetRepository extends EntityRepository
{
    /**
     * Get snippets for agent grouped.
     *
     * @param Person $agent
     * @param $type
     *
     * @return Snippet[]
     */
    public function getSnippetsForAgent(Person $agent, $type = '')
    {
        $qb = $this->getAgentSnippetQb($agent, $type);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Person $agent
     * @param int    $id
     * @param string $type
     *
     * @return Snippet
     */
    public function findSnippetForAgent(Person $agent, $id, $type = '')
    {
        $qb = $this->getAgentSnippetQb($agent, $type);

        $qb->andWhere('s.id = :id');
        $qb->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Person $agent
     * @param array  $ids
     * @param string $type
     *
     * @return Snippet[]
     */
    public function findSnippetsForAgent(Person $agent, $ids, $type = '')
    {
        $qb = $this->getAgentSnippetQb($agent, $type);

        $qb->andWhere('s.id IN (:ids)');
        $qb->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Person $agent
     * @param string $type
     *
     * @return Snippet[]
     */
    public function getSnippetsLabelsForAgent(Person $agent, $type = '')
    {
        $qb = $this->getAgentSnippetQb($agent, $type);

        $qb->select('l.label', 'COUNT(DISTINCT s.id) AS snippet_count', 'l.label AS id');
        $qb->innerJoin('s.labels', 'l');
        $qb->groupBy('l.label');
        $qb->orderBy('l.label');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Person $agent
     * @param string $type
     *
     * @return QueryBuilder
     */
    protected function getAgentSnippetQb($agent, $type = '')
    {
        $agent->loadHelper('AgentTeam');

        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('(s.person = :person')
            ->orWhere('s.isOwnershipGlobal = true)')
            ->setParameter('person', $agent)
        ;
        if ($agent->getAgentTeamIds()) {
            $qb
                ->leftJoin('s.ownershipTeams', 't')
                ->orWhere('t.id IN (:teams)')
                ->setParameter('teams', $agent->getAgentTeamIds())
            ;
        }
        if ($type) {
            $qb
                ->andWhere('s.types LIKE :type')
                ->setParameter('type', '%'.$type.'%')
            ;
        }

        return $qb;
    }
}
