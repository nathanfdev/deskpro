<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

/**
 * Class TicketApprovalsDataService.
 */
class TicketApprovalsDataService extends AbstractDataService
{
    const LAST_24_HOURS    = 'last_24_hours';
    const LAST_WEEK        = 'last_week';
    const LAST_MONTH       = 'last_month';
    const LAST_6_MONTH     = 'last_6_month';
    const LAST_YEAR        = 'last_year';
    const MORE_THAN_1_YEAR = 'more_than_1_year';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TicketApprovalsDataService constructor.
     *
     * @param EntityManager   $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        parent::__construct($em);
        $this->logger = $logger;
    }

    /**
     * @param Person $person
     * @param Brand|null $brand
     *
     * @return int
     */
    public function getApprovalCountWhereUserIsApprover(Person $person = null, $brand = null)
    {
        return $this->generateAndCache([__FUNCTION__, $person], function () use ($person, $brand) {
            $qb = $this->em->createQueryBuilder();
            $time = microtime(true);
            $this->logger->debug('[TicketApprovalsDataService] Count started');

            $qb
                    ->select('COUNT(DISTINCT ta.id)')
                    ->from(TicketApproval::class, 'ta')
                    ->innerJoin('ta.approvers', 'a')
                    ->andWhere('a = :person')
                    ->andWhere('ta.status IN (:statuses)')
                    ->setParameter('person', $person)
                    ->setParameter('statuses', [
                        TicketApproval::STATUS_PENDING,
                        TicketApproval::STATUS_APPROVED,
                        TicketApproval::STATUS_REJECTED,
                    ], Connection::PARAM_STR_ARRAY)
                ;

            if ($brand !== null) {
                $qb->innerJoin('ta.ticket', 't')
                    ->andWhere('t.brand = :brand')
                    ->setParameter('brand', $brand);
            }

            $singleScalarResult = $qb->getQuery()->getSingleScalarResult();

            $this->logger->debug(
                    '[TicketApprovalsDataService] Time taken: '.sprintf('%.5f', microtime(true) - $time)
                );
            $this->logger->debug("[TicketApprovalsDataService] Count: $singleScalarResult");

            return $singleScalarResult;
        }
        );
    }

    /**
     * @param Person|null $person
     * @param null $brand
     *
     * @return mixed|null
     */
    public function getApprovalCountWhereUserNeedAnAction(Person $person = null, $brand = null)
    {
        return $this->generateAndCache([__FUNCTION__, $person], function () use ($person, $brand) {
            $qb = $this->em->createQueryBuilder();
            $time = microtime(true);
            $this->logger->debug('[TicketApprovalsDataService] Count started');

            $qb
                ->select('COUNT(DISTINCT ta.id)')
                ->from(TicketApproval::class, 'ta')
                ->innerJoin('ta.approvers', 'a')
                ->leftJoin('ta.responses', 'r', Join::WITH, 'r.approver = :person')
                ->andWhere('a = :person')
                ->andWhere('ta.status IN (:statuses)')
                ->andWhere('r.approver IS NULL')
                ->setParameter('person', $person)
                ->setParameter('statuses', [
                    TicketApproval::STATUS_PENDING,
                ], Connection::PARAM_STR_ARRAY)
            ;

            if ($brand !== null) {
                $qb->innerJoin('ta.ticket', 't')
                    ->andWhere('t.brand = :brand')
                    ->setParameter('brand', $brand);
            }

            $singleScalarResult = $qb->getQuery()->getSingleScalarResult();

            $this->logger->debug(
                '[TicketApprovalsDataService] Time taken: '.sprintf('%.5f', microtime(true) - $time)
            );
            $this->logger->debug("[TicketApprovalsDataService] Count: $singleScalarResult");

            return $singleScalarResult;
        });
    }

    /**
     * @param Person      $person
     * @param string      $status
     * @param string|null $query
     * @param int         $page
     * @param int         $maxPerPage
     * @param string      $sortParam
     * @param string      $sortDirection
     * @param array       $timeIntervals
     *
     * @return Pagerfanta
     */
    public function getPager(Person $person, $status, $query, $page, $maxPerPage, $sortParam = null, $sortDirection = null, array $timeIntervals = [])
    {
        return $this->generateAndCache(
            [
                __FUNCTION__,
                $person,
                $status,
                $query,
                (int) $page,
                (int) $maxPerPage,
                $sortParam,
                $sortDirection,
                $timeIntervals,
            ],
            function () use ($person, $status, $query, $page, $maxPerPage, $sortParam, $sortDirection, $timeIntervals) {
                $responseQb = $this->em->createQueryBuilder();
                $responseQb
                    ->select('r_r.vote')
                    ->from(ApprovalResponse::class, 'r_r')
                    ->andWhere('r_r.approver = :person')
                    ->andWhere('r_r.approval = ta')
                    ->setMaxResults(1)
                ;

                $qb = $this->em->createQueryBuilder();
                $qb
                    ->select('ta AS approval, t, ag, p')
                    ->addSelect(sprintf('(%s) AS my_vote', $responseQb->getDQL()))
                    ->from(TicketApproval::class, 'ta')
                    ->innerJoin('ta.approvers', 'a')
                    ->innerJoin('ta.ticket', 't')
                    ->leftJoin('t.agent', 'ag')
                    ->innerJoin('t.person', 'p')
                    ->leftJoin('ta.createdBy', 'c')
                    ->andWhere('a = :person')
                    ->andWhere('ta.status = :status')
                    ->setParameter('person', $person)
                    ->setParameter('status', $status)
                ;

                if ($sortParam === 'user') {
                    $qb->orderBy('p.id', $sortDirection);
                } elseif ($sortParam === 'agent') {
                    $qb->orderBy('c.id', $sortDirection);
                } elseif ($sortParam === 'completed') {
                    $qb->orderBy('ta.completedAt', $sortDirection);
                } elseif ($sortParam === 'cancelled') {
                    $qb->orderBy('ta.cancelledAt', $sortDirection);
                }

                if (!empty($query)) {
                    $qb
                        ->andWhere('ta.id = :ref OR ta.name LIKE :query OR ta.description LIKE :query')
                        ->setParameter('query', '%'.$query.'%')
                        ->setParameter('ref', $query)
                    ;
                }

                if ($timeIntervals) {
                    $orX = $qb->expr()->orX();

                    $now         = new \DateTime();
                    $last24hours = new \DateTime('-24 hours');
                    $lastWeek    = new \DateTime('-7 days');
                    $lastMonth   = new \DateTime('-1 month');
                    $last6Month  = new \DateTime('-6 months');
                    $lastYear    = new \DateTime('-1 year');

                    foreach ($timeIntervals as $timeInterval) {
                        if ($timeInterval === self::LAST_24_HOURS) {
                            $orX->add('ta.completedAt BETWEEN :now AND :last_24_hours');
                            $qb->setParameter('now', $now);
                            $qb->setParameter('last_24_hours', $last24hours);
                        } elseif ($timeInterval === self::LAST_WEEK) {
                            $orX->add('ta.completedAt BETWEEN :last_24_hours AND :last_week');
                            $qb->setParameter('last_24_hours', $last24hours);
                            $qb->setParameter('last_week', $lastWeek);
                        } elseif ($timeInterval === self::LAST_MONTH) {
                            $orX->add('ta.completedAt BETWEEN :last_week AND :last_month');
                            $qb->setParameter('last_week', $lastWeek);
                            $qb->setParameter('last_month', $lastMonth);
                        } elseif ($timeInterval === self::LAST_6_MONTH) {
                            $orX->add('ta.completedAt BETWEEN :last_month AND :last_6_month');
                            $qb->setParameter('last_month', $lastMonth);
                            $qb->setParameter('last_6_month', $last6Month);
                        } elseif ($timeInterval === self::LAST_YEAR) {
                            $orX->add('ta.completedAt BETWEEN :last_6_month AND :last_year');
                            $qb->setParameter('last_6_month', $last6Month);
                            $qb->setParameter('last_year', $lastYear);
                        } elseif ($timeInterval === self::MORE_THAN_1_YEAR) {
                            $orX->add('ta.completedAt < :last_year');
                            $qb->setParameter('last_year', $lastYear);
                        }
                    }

                    $qb->andWhere($orX);
                }

                $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                $pager->setMaxPerPage($maxPerPage);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }
}
