<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Usersource as UsersourceEntity;

class PersonUsersourceAssoc extends AbstractEntityRepository
{
    /**
     * Finds the PersonUsersourceAssoc for a given identity.
     * If no association exists, null is returend.
     */
    public function getIdentityAssociation($usersource, $identity)
    {
        $assoc = $this->_em->createQuery('
            SELECT f, p
            FROM DeskPRO:PersonUsersourceAssoc f
            LEFT JOIN f.person p
            WHERE f.usersource = ?1 AND f.identity = ?2
        ')->setMaxResults(1)
          ->setParameter(1, $usersource)
          ->setParameter(2, $identity)
          ->getOneOrNullResult();

        return $assoc;
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return \Application\DeskPRO\Entity\PersonUsersourceAssoc[]
     */
    public function getAssociationsForPerson(\Application\DeskPRO\Entity\Person $person)
    {
        $associations = $this->_em->createQuery('
            SELECT assoc, us
            FROM DeskPRO:PersonUsersourceAssoc assoc
            LEFT JOIN assoc.usersource us
            WHERE assoc.person = ?0
        ')->execute([$person]);

        return $associations;
    }

    public function getAssociationForPersonUsersourcePair(\Application\DeskPRO\Entity\Person $person, UsersourceEntity $usersource)
    {
        $association = $this->_em->createQuery('
            SELECT assoc
            FROM DeskPRO:PersonUsersourceAssoc assoc
            WHERE assoc.person = ?0
            AND assoc.usersource = ?1
        ')->execute([$person, $usersource]);

        if (count($association)) {
            return current($association);
        }

        return;
    }

    /**
     * @param \DateTime $last_updated
     *
     * @return \Application\DeskPRO\Entity\PersonUsersourceAssoc[]
     */
    public function getAssociationsUpdatedBefore(UsersourceEntity $usersource, \DateTime $last_updated)
    {
        return $this->_em->createQuery('
            SELECT assoc
            FROM DeskPRO:PersonUsersourceAssoc assoc
            WHERE assoc.date_updated < :last_updated
            AND assoc.usersource = :usersource
        ')
            ->setParameter('last_updated', $last_updated)
            ->setParameter('usersource', $usersource)
            ->execute();
    }
}
