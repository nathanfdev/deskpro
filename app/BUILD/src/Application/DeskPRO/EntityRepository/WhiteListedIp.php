<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;

class WhiteListedIp extends AbstractEntityRepository
{
    public function getIpsForPerson(PersonEntity $person)
    {
        $output = [];

        $ip_array = $this->getEntityManager()->createQuery('
            SELECT w.ip_address
            FROM DeskPRO:WhiteListedIp w
            WHERE w.person = ?1
            ORDER BY w.id DESC
        ')->execute([1 => $person]);

        foreach ($ip_array as $ip) {
            $output[] = $ip['ip_address'];
        }

        return $output;
    }
}
