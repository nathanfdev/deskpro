<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

/**
 * Class TicketApprovalsDataService
 *
 * @package DeskPRO\Bundle\AppBundle\DataService
 */
class TicketApprovalsDataService extends AbstractDataService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TicketApprovalsDataService constructor.
     *
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        parent::__construct($em);
        $this->logger = $logger;
    }

    /**
     * @param Person $person
     * @return int
     */
    public function getApprovalCountWhereUserIsApprover(Person $person)
    {
        return $this->generateAndCache([__FUNCTION__, $person], function () use ($person) {
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
     * @param Person $person
     * @param string $status
     * @param string|null $query
     * @param int $page
     * @param int $maxPerPage
     * @return Pagerfanta
     */
    public function getPager(Person $person, $status, $query, $page, $maxPerPage)
    {
        $em = $this->em;

        return $this->generateAndCache(
            [
                __FUNCTION__,
                $person,
                $status,
                $query,
                (int) $page,
                (int) $maxPerPage,
            ],
            function () use ($em, $person, $status, $query, $page, $maxPerPage) {
                $qb = $em->createQueryBuilder();
                $responseQb = $em->createQueryBuilder();

                $responseQb
                    ->select('r_r.vote')
                    ->from(ApprovalResponse::class, 'r_r')
                    ->andWhere('r_r.approver = :person')
                    ->andWhere('r_r.approval = ta')
                    ->setMaxResults(1)
                ;

                $qb
                    ->select('ta AS approval, a, t, ag, p')
                    ->addSelect(sprintf('(%s) AS my_vote', $responseQb->getDQL()))
                    ->from(TicketApproval::class, 'ta')
                    ->innerJoin('ta.approvers', 'a')
                    ->innerJoin('ta.ticket', 't')
                    ->leftJoin('t.agent', 'ag')
                    ->innerJoin('t.person', 'p')
                    ->andWhere('a = :person')
                    ->andWhere('ta.status = :status')
                    ->setParameter('person', $person)
                    ->setParameter('status', $status)
                ;

                if (!empty($query)) {
                    $qb
                        ->andWhere('ta.id = :ref OR ta.name LIKE :query OR ta.description LIKE :query')
                        ->setParameter('query', '%'.$query.'%')
                        ->setParameter('ref', $query)
                    ;
                }

                $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                $pager->setMaxPerPage($maxPerPage);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }
}
