<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SnippetRepository.
 */
class SnippetRepository extends AbstractEntityRepository
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
