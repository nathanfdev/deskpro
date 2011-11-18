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

    public function findDealsForPerson(Entity\Person $person, $status = 0)
    {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(d) types, dt.name, dt.id')
                    ->from('DeskPRO:Deal', 'd')
                    ->innerJoin('d.assigned_agent', 'p')
                    ->innerJoin('d.deal_type', 'dt')
                    ->where('p.id = :person_id')
                    ->andWhere('d.status = :status')
                    ->groupBy('d.deal_type')
                    ->setParameters(array('person_id' => $person['id'], 'status' => $status))
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

    public function findDealsForOther(Entity\Person $person, $status = 0)
    {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(d) types, dt.name, dt.id')
                    ->from('DeskPRO:Deal', 'd')
                    ->leftJoin('d.assigned_agent', 'p')
                    ->innerJoin('d.deal_type', 'dt')
                    ->where('p.id IS NULL OR p.id != :person_id')
                    ->andWhere('d.status = :status')
                    ->groupBy('d.deal_type')
                    ->setParameters(array('person_id' => $person['id'], 'status' => $status))
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

    public function countDealsForPerson(Entity\Person $person, $status = 0)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(d)')
                    ->from('DeskPRO:Deal', 'd')
                    ->innerJoin('d.assigned_agent', 'p')
                    ->innerJoin('d.deal_type', 'dt')
                    ->where('p.id = :person_id')
                    ->andWhere('d.status = :status')
                    //->groupBy('d.deal_type')
                    ->setParameters(array('person_id' => $person['id'], 'status' => $status))
                    ;
            $query = $qb->getQuery(); //print $query->getSQL();exit;
            return $query->getSingleScalarResult();
    
    }

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */

    public function countDealsForOther(Entity\Person $person, $status = 0)
    {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('COUNT(d)')
                    ->from('DeskPRO:Deal', 'd')
                    ->leftJoin('d.assigned_agent', 'p')
                    ->innerJoin('d.deal_type', 'dt')
                    ->where('p.id IS NULL OR p.id != :person_id')
                    ->andWhere('d.status = :status')                    
                    ->setParameters(array('person_id' => $person['id'], 'status' => $status))
                    ;
            $query = $qb->getQuery(); //print $query->getSQL();
            return $query->getSingleScalarResult();

    }

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */

    public function filterDealsForPerson(Entity\Person $person, $status = 0, $deal_type_id = null) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d')
                ->from('DeskPRO:Deal', 'd')
                ->innerJoin('d.assigned_agent', 'p')
                ->innerJoin('d.deal_type', 'dt')
                ->where('p.id = :person_id');
        $qb->setParameter('person_id', $person['id']);

        if ($status >= 0) {
            $qb->andWhere('d.status = :status');
            $qb->setParameter('status', $status);
        }
        else
        {
            $qb->andWhere('d.status > :status');
            $qb->setParameter('status', 0);
        }

        if ($deal_type_id) {
            $qb->andWhere('dt.id = :deal_type_id');
            $qb->setParameter('deal_type_id', $deal_type_id);
        }

        $query = $qb->getQuery(); //print $query->getSQL();
        return $query->getResult();
    }

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */

    public function filterDealsForOther(Entity\Person $person, $status = 0, $deal_type_id = null) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d')
                ->from('DeskPRO:Deal', 'd')
                ->leftJoin('d.assigned_agent', 'p')
                ->innerJoin('d.deal_type', 'dt')
                ->where('p.id IS NULL OR p.id != :person_id')
                ->setParameter('person_id', $person['id']);
                
        if ($status >= 0) {
            $qb->andWhere('d.status = :status');
            $qb->setParameter('status', $status);
        }
        else
        {
            $qb->andWhere('d.status > :status');
            $qb->setParameter('status', 0);
        }

        if ($deal_type_id) {
            $qb->andWhere('dt.id = :deal_type_id');
            $qb->setParameter('deal_type_id', $deal_type_id);
        }

        $query = $qb->getQuery(); //print $query->getSQL();
        return $query->getResult();
    }

    public function findPersonInDeal(Entity\Person $person, $deal_id = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(p)')
                    ->from('DeskPRO:Deal', 'd')
                    ->innerJoin('d.peoples', 'p')
                    ->where('p.id = :person_id')
                    ->andWhere('d.id = :deal_id')
                    ->setParameters(array('person_id' => $person['id'], 'deal_id' => $deal_id))
                    ;
            $query = $qb->getQuery(); //print $query->getSQL();exit;
            return $query->getSingleScalarResult();
    }

}