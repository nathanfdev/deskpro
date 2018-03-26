<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\SendmailSource;

use Doctrine\ORM\EntityManager;

class Finder
{
    /**
     * @var \Application\DeskPRO\Email\EmailSource\FinderFilter
     */
    private $filter;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     * @param FinderFilter  $filter
     */
    public function __construct(EntityManager $em, FinderFilter $filter)
    {
        $this->em     = $em;
        $this->filter = $filter;
    }

    /**
     * @return array
     */
    public function getPageInfo()
    {
        $q = $this->getQb();
        $q->select('COUNT(s)');

        $count     = (int) $q->getQuery()->getSingleScalarResult();
        $num_pages = ceil($count / $this->filter->getPerPage());

        return [
            'count'     => $count,
            'num_pages' => $num_pages,
        ];
    }

    /**
     * @return \Application\EmailBundle\Entity\SendmailSource[]
     */
    public function getResults()
    {
        $q = $this->getQb();
        $q->select('s')
            ->orderBy('s.id', 'DESC')
            ->setMaxResults($this->filter->getPerPage())
            ->setFirstResult(($this->filter->getPage() - 1) * $this->filter->getPerPage());

        return $q->getQuery()->execute();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQb()
    {
        $q = $this->em->createQueryBuilder();
        $q->from('EmailBundle:SendmailSource', 's');

        if ($opt = $this->filter->getStatuses()) {
            $q->andWhere('s.status IN (:statuses)');
            $q->setParameter('statuses', $opt);
        }

        $d1 = $this->filter->getDateStart();
        $d2 = $this->filter->getDateEnd();

        if ($d1 && $d2) {
            if ($d2 < $d1) {
                $tmp = $d2;
                $d2  = $d1;
                $d1  = $tmp;
            }

            $q->andWhere('s.date_created BETWEEN :date1 AND :date2');
            $q->setParameter('date1', $d1);
            $q->setParameter('date2', $d2);
        } elseif ($d1) {
            $q->andWhere('s.date_created >= :date1');
            $q->setParameter('date1', $d1);
        } elseif ($d2) {
            $q->andWhere('s.date_created <= :date2');
            $q->setParameter('date2', $d1);
        }

        if ($opt = $this->filter->getFrom()) {
            $q->andWhere('s.header_from LIKE :from');
            $q->setParameter('from', "%$opt%");
        }

        if ($opt = $this->filter->getTo()) {
            $q->andWhere('s.header_to LIKE :to');
            $q->setParameter('to', "%$opt%");
        }

        if ($opt = $this->filter->getSubject()) {
            $q->andWhere('s.header_subject LIKE :subject');
            $q->setParameter('subject', "%$opt%");
        }

        return $q;
    }
}
