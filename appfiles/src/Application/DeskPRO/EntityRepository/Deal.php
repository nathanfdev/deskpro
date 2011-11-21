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

class Deal extends EntityRepository {

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */
    public function findDealsForPerson(Entity\Person $person, $status = 0) {
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
    public function findDealsForOther(Entity\Person $person, $status = 0) {
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
    public function countDealsForPerson(Entity\Person $person, $status = 0) {
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
    public function countDealsForOther(Entity\Person $person, $status = 0) {
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
    public function filterDealsForPerson(Entity\Person $person, $status = 0, $deal_type_id = null, $order_by = 'date_created') {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d')
                ->from('DeskPRO:Deal', 'd')
                ->innerJoin('d.assigned_agent', 'p')
                ->innerJoin('d.deal_type', 'dt')
                ->leftJoin('d.deal_stage', 'ds')
                ->where('p.id = :person_id');
        $qb->setParameter('person_id', $person['id']);

        if ($status >= 0) {
            $qb->andWhere('d.status = :status');
            $qb->setParameter('status', $status);
        } else {
            $qb->andWhere('d.status > :status');
            $qb->setParameter('status', 0);
        }

        if ($deal_type_id) {
            $qb->andWhere('dt.id = :deal_type_id');
            $qb->setParameter('deal_type_id', $deal_type_id);
        }

        switch ($order_by) {
            case 'date_created':
                $qb->orderBy('d.date_created');
                break;
            case 'title':
                $qb->orderBy('d.title');
                break;
            case 'deal_size':
                $qb->orderBy('d.deal_value');
                break;
            case 'deal_type':
                $qb->orderBy('dt.name');
                break;
        }

        $query = $qb->getQuery(); //print $query->getSQL(); exit;
        return $query->getResult();
    }

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */
    public function filterDealsForOther(Entity\Person $person, $status = 0, $deal_type_id = null, $order_by = 'date_created') {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d')
                ->from('DeskPRO:Deal', 'd')
                ->leftJoin('d.assigned_agent', 'p')
                ->innerJoin('d.deal_type', 'dt')
                ->leftJoin('d.deal_stage', 'ds')
                ->where('p.id IS NULL OR p.id != :person_id')
                ->setParameter('person_id', $person['id']);

        if ($status >= 0) {
            $qb->andWhere('d.status = :status');
            $qb->setParameter('status', $status);
        } else {
            $qb->andWhere('d.status > :status');
            $qb->setParameter('status', 0);
        }

        if ($deal_type_id) {
            $qb->andWhere('dt.id = :deal_type_id');
            $qb->setParameter('deal_type_id', $deal_type_id);
        }

        switch ($order_by) {
            case 'date_created':
                $qb->orderBy('d.date_created');
                break;
            case 'title':
                $qb->orderBy('d.title');
                break;
            case 'deal_size':
                $qb->orderBy('d.deal_value');
                break;
            case 'deal_type':
                $qb->orderBy('dt.name');
                break;
        }

        $query = $qb->getQuery(); //print $query->getSQL();
        return $query->getResult();
    }

    public function findPersonInDeal(Entity\Person $person, $deal_id = null) {
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

    public function findOrganizationInDeal(Entity\Organization $organization, $deal_id = null) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(o)')
                ->from('DeskPRO:Deal', 'd')
                ->innerJoin('d.organizations', 'o')
                ->where('o.id = :org_id')
                ->andWhere('d.id = :deal_id')
                ->setParameters(array('org_id' => $organization['id'], 'deal_id' => $deal_id))
        ;
        $query = $qb->getQuery(); 
        return $query->getSingleScalarResult();
    }

    /**
     * Group By deal accor to the filter.
     *
     * @param Entity\Person $person
     * @param <type> $status
     * @param <type> $deal_type_id
     * @param <type> $group_by
     * @return Collection
     */
    public function groupByDealsForPerson(Entity\Person $person, $status = 0, $deal_type_id = null, $group_by = null) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->from('DeskPRO:Deal', 'd')
                ->innerJoin('d.assigned_agent', 'p')
                ->innerJoin('d.deal_type', 'dt')
                ->leftJoin('d.deal_stage', 'ds')
                ->where('p.id = :person_id');

        $qb->setParameter('person_id', $person['id']);

        if ($status >= 0) {
            $qb->andWhere('d.status = :status');
            $qb->setParameter('status', $status);
        } else {
            $qb->andWhere('d.status > :status');
            $qb->setParameter('status', 0);
        }

        if ($deal_type_id) {
            $qb->andWhere('dt.id = :deal_type_id');
            $qb->setParameter('deal_type_id', $deal_type_id);
        }

        switch ($group_by) {
            case 'deal_stage':
                $qb->select('COUNT(d), ds.name AS name, ds.id AS id');
                $qb->groupBy('d.deal_stage');
                break;
            case 'deal_type':
                $qb->select('COUNT(d), dt.name AS name, dt.id AS id');
                $qb->groupBy('d.deal_type');
                break;
            default:
                $qb->select('COUNT(d)');
                break;
        }

        $query = $qb->getQuery(); //print $query->getSQL(); exit;
        return $query->getScalarResult();
    }

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */
    public function groupByDealsForOther(Entity\Person $person, $status = 0, $deal_type_id = null, $group_by = null) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d')
                ->from('DeskPRO:Deal', 'd')
                ->leftJoin('d.assigned_agent', 'p')
                ->innerJoin('d.deal_type', 'dt')
                ->leftJoin('d.deal_stage', 'ds')
                ->where('p.id IS NULL OR p.id != :person_id')
                ->setParameter('person_id', $person['id']);

        if ($status >= 0) {
            $qb->andWhere('d.status = :status');
            $qb->setParameter('status', $status);
        } else {
            $qb->andWhere('d.status > :status');
            $qb->setParameter('status', 0);
        }

        if ($deal_type_id) {
            $qb->andWhere('dt.id = :deal_type_id');
            $qb->setParameter('deal_type_id', $deal_type_id);
        }

        switch ($group_by) {
            case 'deal_stage':
                $qb->select('COUNT(d), ds.name AS name, ds.id AS id');
                $qb->groupBy('d.deal_stage');
                break;
            case 'deal_type':
                $qb->select('COUNT(d), dt.name AS name, dt.id AS id');
                $qb->groupBy('d.deal_type');
                break;
            case 'assigned_agent':
                $qb->select('COUNT(d), p.name AS name, p.id AS id');
                $qb->groupBy('d.assigned_agent');
                break;
            default:
                $qb->select('COUNT(d)');
                break;
        }

        $query = $qb->getQuery(); //print $query->getSQL(); exit;
        return $query->getScalarResult();
    }

    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */
    public function filterGroupByDealsForPerson(Entity\Person $person, $status = 0, $deal_type_id = null, $group_by = null, $set_group_option = 0) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d')
                ->from('DeskPRO:Deal', 'd')
                ->innerJoin('d.assigned_agent', 'p')
                ->innerJoin('d.deal_type', 'dt')
                ->leftJoin('d.deal_stage', 'ds')
                ->where('p.id = :person_id');
        $qb->setParameter('person_id', $person['id']);
        $qb->orderBy('d.date_created');

        if ($status >= 0) {
            $qb->andWhere('d.status = :status');
            $qb->setParameter('status', $status);
        } else {
            $qb->andWhere('d.status > :status');
            $qb->setParameter('status', 0);
        }

        if ($deal_type_id) {
            $qb->andWhere('dt.id = :deal_type_id');
            $qb->setParameter('deal_type_id', $deal_type_id);
        }

        if ($set_group_option) {
            switch ($group_by) {
                case 'deal_stage':
                    $qb->andWhere('ds.id = :deal_stage');
                    $qb->setParameter('deal_stage', $set_group_option);
                    break;
                case 'deal_type':
                    $qb->andWhere('dt.id = :deal_type');
                    $qb->setParameter('deal_type', $set_group_option);
                    break;
            }
        }

        $query = $qb->getQuery(); //print $query->getSQL(); exit;
        return $query->getResult();
    }


    /**
     * Find pending tasks assigned to the person.
     *
     * @param Person $person The person
     * @return Array
     */
    public function filterGroupByDealsForOther(Entity\Person $person, $status = 0, $deal_type_id = null, $group_by = null, $set_group_option = 0) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d')
                ->from('DeskPRO:Deal', 'd')
                ->leftJoin('d.assigned_agent', 'p')
                ->innerJoin('d.deal_type', 'dt')
                ->leftJoin('d.deal_stage', 'ds')
                ->where('p.id IS NULL OR p.id != :person_id')
                ->setParameter('person_id', $person['id']);
        $qb->orderBy('d.date_created');

        if ($status >= 0) {
            $qb->andWhere('d.status = :status');
            $qb->setParameter('status', $status);
        } else {
            $qb->andWhere('d.status > :status');
            $qb->setParameter('status', 0);
        }

        if ($deal_type_id) {
            $qb->andWhere('dt.id = :deal_type_id');
            $qb->setParameter('deal_type_id', $deal_type_id);
        }

        if ($set_group_option) {
            switch ($group_by) {
                case 'deal_stage':
                        $qb->andWhere('ds.id = :deal_stage');
                        $qb->setParameter('deal_stage', $set_group_option);
                        break;
                case 'deal_type':
                    $qb->andWhere('dt.id = :deal_type');
                    $qb->setParameter('deal_type', $set_group_option);
                    break;
                case 'assigned_agent':
                   $qb->andWhere('p.id = :assigned_agent');
                    $qb->setParameter('assigned_agent', $set_group_option);
                    break;
            }
        }


        $query = $qb->getQuery(); //print $query->getSQL();
        return $query->getResult();
    }

}