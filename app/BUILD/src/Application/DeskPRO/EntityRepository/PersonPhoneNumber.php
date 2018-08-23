<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class PersonPhoneNumber extends AbstractEntityRepository
{
    public function findByNumber($number)
    {
        $tempNumber      = new \Application\DeskPRO\Entity\PersonPhoneNumber($number);
        $formattedNumber = $tempNumber->number;

        $phone_number = $this->getEntityManager()->createQuery(
            '
            SELECT p
            FROM DeskPRO:PhoneNumber p
            WHERE p.number = :number
        '
        )
        ->setMaxResults(1)
        ->setParameter('number', $formattedNumber)
        ->getOneOrNullResult();

        return $phone_number;
    }
}
