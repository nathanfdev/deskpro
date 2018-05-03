<?php

namespace DeskPRO\Bundle\ApiBundle\Traits;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trait AgentChatTrait.
 *
 * @method Person getUser()
 *
 * @property ContainerInterface $container
 */
trait AgentChatFiltersTrait
{
    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     */
    public function applyParticipantFilters(QueryBuilder $qb, $alias)
    {
        $person      = $this->getUser();
        $departments = $this->container->get('data.departments')->getDepartmentsForPerson($person);

        $qb
            ->leftJoin("$alias.participants", 'participants')
            ->andWhere($qb->expr()->orX(
                'participants.person = :person',
                'participants.team IN (:agent_teams)',
                'participants.department IN (:departments)'
            ))
            ->orWhere("$alias.type = 'everyone'")
            ->setParameter('person', $person)
            ->setParameter('agent_teams', $person->getTeams())
            ->setParameter('departments', $departments)
        ;
    }
}
