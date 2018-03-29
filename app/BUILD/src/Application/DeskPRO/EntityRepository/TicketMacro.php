<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity;
use Application\DeskPRO\People\Helpers\AgentPermissions;

class TicketMacro extends AbstractEntityRepository
{
    public function getMacros()
    {
        return $this->_em->createQuery('
            SELECT m
            FROM DeskPRO:TicketMacro m
            ORDER BY m.title ASC
        ')->execute();
    }

    /**
     * @param Entity\Person $person
     *
     * @return Entity\TicketMacro
     */
    public function getMacrosForPerson(Entity\Person $person)
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->select('m')
            ->where('m.is_enabled = 1')
            ->setParameter('person_id', $person)
        ;

        $ownerWhere = $qb->expr()->orX(
            'm.person = :person_id',
            'm.is_global = 1'
        );

        /* @var AgentPermissions $helper */
        $person->loadHelper('AgentPermissions');
        $helper = $person->getHelper('AgentPermissions');

        $departmentIds = $helper->getAllowedDepartments('tickets', true);
        if ($departmentIds) {
            $ownerWhere->add('m.department IN (:department_ids)');
            $qb->setParameter('department_ids', $departmentIds);
        }

        $qb->andWhere($ownerWhere);

        return $qb->getQuery()->getResult();
    }
}
