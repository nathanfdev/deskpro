<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Email\SendmailQueue;

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

        $count     = (int)$q->getQuery()->getSingleScalarResult();
        $num_pages = ceil($count / $this->filter->getPerPage());

        return array(
            'count'     => $count,
            'num_pages' => $num_pages
        );
    }


    /**
     * @return \Application\DeskPRO\Entity\SendmailQueue[]
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
        $q->from('DeskPRO:SendmailQueue', 's');

        if ($opt = $this->filter->getStatuses()) {
            $q->andWhere('s.status IN (:statuses)');
            $q->setParameter('statuses', $opt);
        }

        $d1 = $this->filter->getDateStart();
        $d2 = $this->filter->getDateEnd();

        if ($d1 && $d2) {
            if ($d2 < $d1) {
                $tmp = $d2;
                $d2 = $d1;
                $d1 = $tmp;
            }

            $q->andWhere("s.date_created BETWEEN :date1 AND :date2");
            $q->setParameter('date1', $d1);
            $q->setParameter('date2', $d2);
        } elseif ($d1) {
            $q->andWhere("s.date_created >= :date1");
            $q->setParameter('date1', $d1);
        } elseif ($d2) {
            $q->andWhere("s.date_created <= :date2");
            $q->setParameter('date2', $d1);
        }

        if ($opt = $this->filter->getFrom()) {
            $q->andWhere('s.from_address LIKE :from');
            $q->setParameter('from', "%$opt%");
        }

        if ($opt = $this->filter->getTo()) {
            $q->andWhere('s.to_address LIKE :to');
            $q->setParameter('to', "%$opt%");
        }

        if ($opt = $this->filter->getSubject()) {
            $q->andWhere('s.subject LIKE :subject');
            $q->setParameter('subject', "%$opt%");
        }

        return $q;
    }
}
