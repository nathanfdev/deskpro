<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Symfony\Component\Validator\Constraints\DateTime;

use Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity;

class Deal extends EntityRepository
{

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */

    public function findOenDealsForPerson(Entity\Person $person)
    {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(d) types, dt.name')
                    ->from('DeskPRO:Deal', 'd')
                    ->innerJoin('d.person', 'p')
                    ->innerJoin('d.deal_type', 'dt')
                    ->where('p.id = :person_id')
                    ->andWhere('d.status = :status')
                    ->groupBy('d.deal_type')
                    ->setParameters(array('person_id' => $person['id'], 'status' => Entity\Deal::DEAL_OPEN))
                    ;
            $query = $qb->getQuery(); //print $query->getSQL();
            return $query->getScalarResult();

    }

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */

    public function findOenDealsForOther(Entity\Person $person)
    {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(d) types, dt.name')
                    ->from('DeskPRO:Deal', 'd')
                    ->innerJoin('d.person', 'p')
                    ->innerJoin('d.deal_type', 'dt')
                    ->where('p.id != :person_id')
                    ->andWhere('d.status = :status')
                    ->groupBy('d.deal_type')
                    ->setParameters(array('person_id' => $person['id'], 'status' => Entity\Deal::DEAL_OPEN))
                    ;
            $query = $qb->getQuery(); //print $query->getSQL();
            return $query->getScalarResult();

    }
}