<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

class TicketsDataService extends AbstractDataService
{
    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TicketsDataService constructor.
     *
     * @param EntityManager   $em
     * @param BrandStack      $brandStack
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, BrandStack $brandStack, LoggerInterface $logger)
    {
        parent::__construct($em);

        $this->brandStack = $brandStack;
        $this->logger     = $logger;
    }

    protected function ignoreTicketsWithOnlyAgentNotes(QueryBuilder $qb)
    {
        // make sure a message by the agent or the user has been made via using the last reply columns.
        $qb->andWhere('(t.date_last_agent_reply IS NOT NULL OR t.date_last_user_reply IS NOT NULL)');
    }

    /**
     * @param Person       $person
     * @param TicketFilter $filter
     * @param int          $page
     * @param int          $maxPerPage
     * @param bool         $ignoreOnlyNotes if true, we ignore tickets that only have agent messages (USE IN PORTAL)
     *
     * @return Pagerfanta
     */
    public function getPager(Person $person, TicketFilter $filter, $page, $maxPerPage, $ignoreOnlyNotes = true)
    {
        $em = $this->em;

        $brand = $this->brandStack->getActive()->getBrand();

        return $this->generateAndCache(
            [
                'getPager',
                $person,
                $filter,
                (int) $page,
                (int) $maxPerPage,
                (bool) $ignoreOnlyNotes,
                $brand,
            ],
            function () use ($em, $person, $filter, $page, $maxPerPage, $ignoreOnlyNotes, $brand) {
                $qb = $em->createQueryBuilder();
                $qb->select('t')
                    ->from(Ticket::class, 't')
                    ->join('t.person', 'p')
                    ->where('t.status != :hidden')->setParameter('hidden', Ticket::STATUS_HIDDEN)
                    ->andWhere('t.brand = :brand')->setParameter('brand', $brand);

                if ($ignoreOnlyNotes) {
                    $this->ignoreTicketsWithOnlyAgentNotes($qb);
                }

                // type
                if (TicketFilter::TYPE_OWN === $filter->getType()) {
                    if ($person->isAgent()) {
                        // agents only their own tickets
                        $qb->andWhere('t.person = :person')->setParameter('person', $person);
                    } else {
                        /** @var Connection $connection */
                        $connection = $em->getConnection();
                        if (!$person->getOrganization() || !$person->isOrganizationManager()) {
                            $parts = [
                                '(SELECT id FROM tickets WHERE person_id = ? ORDER BY id DESC LIMIT 2000)',
                                '(SELECT ticket_id FROM tickets_participants WHERE person_id = ? ORDER BY ticket_id DESC LIMIT 2000)',
                            ];

                            $params = [$person->getId(), $person->getId()];
                            $partsUnion = implode("\nUNION\n", $parts);
                            $ids = $connection->fetchAllCol("SELECT DISTINCT id FROM ($partsUnion) AS t", $params);

                            $qb->andWhere('t.id IN (:ids)');
                            $qb->setParameter('ids', $ids);
                        } else {

                            // but if they are an org manager, ignore the org tickets unless created directly by them (they show in org page, filtered below)
                            $qb->leftJoin('t.participants', 'part');
                            $qb->andWhere('t.person = :person OR (part.person = :person AND (t.organization != :organization OR t.organization IS NULL))');
                            $qb->setParameter('person', $person)->setParameter('organization', $person->getOrganization());
                        }
                    }
                } else {
                    // its assumed that if you send in a person with an "organization" type filter that they have an
                    // organization and are a manger. ensure the controller/calling-code has this secured
                    $qb->andWhere('t.organization = :organization')->setParameter('organization', $person->getOrganization());
                }

                // category
                switch ($filter->getCategory()) {

                    case TicketFilter::CATEGORY_AWAITING_AGENT:
                        $qb->andWhere('t.status = :status')->setParameter('status', Ticket::STATUS_AWAITING_AGENT);
                        break;

                    case TicketFilter::CATEGORY_RESOLVED:
                        $qb->andWhere('t.status IN (:status)')->setParameter('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_ARCHIVED]);
                        break;

                    case TicketFilter::CATEGORY_AWAITING_USER:
                    default:
                        $qb->andWhere('t.status = :status')->setParameter('status', Ticket::STATUS_AWAITING_USER);
                        break;

                }

                // search query
                if ($q = $filter->getSearchQuery()) {
                    $qb->join('t.messages', 'm');
                    $qb->andWhere('(t.ref = :ref OR t.id = :ref OR t.subject LIKE :query OR (m.message LIKE :query AND m.is_agent_note = 0))')
                        ->setParameter('query', '%'.$q.'%')
                        ->setParameter('ref', $q)
                    ;
                }

                // sort
                switch ($filter->getSort()) {
                    case TicketFilter::SORT_DEPARTMENT:
                        $qb->join('t.department', 'd');
                        $qb->orderBy('d.title', $filter->getSortDirection());
                        break;

                    case TicketFilter::SORT_SUBJECT:
                        $qb->orderBy('t.subject', $filter->getSortDirection());
                        break;

                    case TicketFilter::SORT_CREATED:
                        $qb->orderBy('t.date_created', $filter->getSortDirection());
                        break;

                    case TicketFilter::SORT_LAST_USER:
                        $qb->addOrderBy('t.date_last_user_reply', $filter->getSortDirection());
                        break;

                    case TicketFilter::SORT_LAST_AGENT:
                        $qb->addOrderBy('t.date_last_agent_reply', $filter->getSortDirection());
                        break;

                    case TicketFilter::SORT_USER:
                        $qb->addOrderBy('p.name', $filter->getSortDirection());
                        break;

                    case TicketFilter::SORT_AGENT:
                        $qb->orderBy('t.agent', $filter->getSortDirection());
                        break;
                    // TODO: last activity algorithm (same as Ticket::getLastActivityDate())
                    case TicketFilter::SORT_ACTIVITY:
                        $qb->addOrderBy('t.date_last_user_reply', $filter->getSortDirection());
                        $qb->addOrderBy('t.date_last_agent_reply', $filter->getSortDirection());
                        $qb->addOrderBy('t.date_created', $filter->getSortDirection());
                        break;
                }

                $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                $pager->setMaxPerPage($maxPerPage);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * Returns the count of tickets that can be seen by the user by default. You can optionally provide a status to count on.
     *
     * @param Person $person
     * @param string $status          "open" [awaiting user or agent], "all" [open + resolved], or a specific status
     * @param bool   $ignoreOnlyNotes if true, we ignore tickets that only have agent messages (USE IN PORTAL)
     *
     * @return int|null
     */
    public function getTicketCount(Person $person, $status = 'all', $ignoreOnlyNotes = true)
    {
        $em = $this->em;

        $brand = $this->brandStack->getActive()->getBrand();

        return $this->generateAndCache(
            [
                'getTicketCount',
                $person,
                $status,
                $ignoreOnlyNotes,
                $brand,
            ],
            function () use ($em, $person, $status, $ignoreOnlyNotes, $brand) {
                $qb = $em->createQueryBuilder();
                $time = microtime(true);
                $this->logger->debug('[TicketsDataService] Count started');
                if ('open' === $status) {
                    $statusList = [
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_AWAITING_USER,
                    ];
                } elseif ('all' !== $status) {
                    $statusList = [$status];
                } else {
                    $statusList = [
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_RESOLVED,
                        Ticket::STATUS_ARCHIVED,
                        Ticket::STATUS_AWAITING_USER,
                    ];
                }

                $qb->select($qb->expr()->countDistinct('t.id'))
                    ->from(Ticket::class, 't')
                    ->andWhere('t.status IN (:status_list)')->setParameter('status_list', $statusList)
                ;

                if ($brand && $brand->getId()) {
                    $qb->andWhere('t.brand = :brand');
                    $qb->setParameter('brand', $brand);
                }

                if ($ignoreOnlyNotes) {
                    $this->ignoreTicketsWithOnlyAgentNotes($qb);
                }

                if ($person->isAgent()) {
                    $this->logger->debug('[TicketsDataService] Agent tickets counting');
                    $qb->andWhere('t.person = :person')->setParameter('person', $person);
                } else {
                    /** @var Connection $connection */
                    $connection = $em->getConnection();
                    if (!$person->getOrganization() || !$person->isOrganizationManager()) {
                        $this->logger->debug('[TicketsDataService] No organization count, using UNION');

                        $parts = [
                            '(SELECT id FROM tickets WHERE person_id = ? ORDER BY id DESC)',
                            '(SELECT ticket_id FROM tickets_participants WHERE person_id = ? ORDER BY ticket_id DESC)',
                        ];
                        $params = [$person->getId(), $person->getId()];
                        $partsUnion = implode("\nUNION\n", $parts);
                        $ids = $connection->fetchAllCol("SELECT DISTINCT id FROM ($partsUnion) AS t", $params);

                        $qb->andWhere('t.id IN (:ids)');
                        $qb->setParameter('ids', $ids);
                    } else {
                        $this->logger->debug('[TicketsDataService] Organization count, using JOIN');

                        // but if they are an org manager, ignore the org tickets unless created directly by them (they show in org page, filtered below)
                        $qb->leftJoin('t.participants', 'part');
                        $qb->andWhere('t.person = :person OR (part.person = :person AND (t.organization != :organization OR t.organization IS NULL))');
                        $qb->setParameter('person', $person)->setParameter('organization', $person->getOrganization());
                    }
                }

                $qb->distinct(true);

                $singleScalarResult = $qb->getQuery()->getSingleScalarResult();

                $str = '[TicketsDataService] Time taken: '.sprintf('%.5f', microtime(true) - $time);
                $this->logger->debug($str);
                $this->logger->debug("[TicketsDataService] Count: $singleScalarResult");

                return $singleScalarResult;
            }
        );
    }

    /**
     * Returns the count of tickets that can be seen by the organization manager (stuff that would show up
     * if they clicked "Switch to Organization" in the ticket list).
     *
     * @param Person $person
     * @param string $status          "open" [awaiting user or agent], "all" [open + resolved], or a specific status
     * @param bool   $ignoreOnlyNotes if true, we ignore tickets that only have agent messages (USE IN PORTAL)
     *
     * @return int|null
     */
    public function getOrganizationTicketCount(Person $person, $status = 'all', $ignoreOnlyNotes = true)
    {
        if (!$person->getOrganization() || !$person->isOrganizationManager()) {
            return 0;
        }

        $em = $this->em;

        $brand = $this->brandStack->getActive()->getBrand();

        return $this->generateAndCache(
            [
                'getOrganizationTicketCount',
                $person,
                $status,
                $ignoreOnlyNotes,
                $brand,
            ],
            function () use ($em, $person, $status, $ignoreOnlyNotes, $brand) {
                $qb = $em->createQueryBuilder();

                if ('open' === $status) {
                    $statusList = [
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_AWAITING_USER,
                    ];
                } elseif ('all' !== $status) {
                    $statusList = [$status];
                } else {
                    $statusList = [
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_RESOLVED,
                        Ticket::STATUS_AWAITING_USER,
                    ];
                }

                $qb->select($qb->expr()->countDistinct('t.id'))
                    ->from(Ticket::class, 't')
                    ->andWhere('t.status IN (:status_list)')->setParameter('status_list', $statusList)
                    ->andWhere('t.brand = :brand')->setParameter('brand', $brand);

                if ($ignoreOnlyNotes) {
                    $this->ignoreTicketsWithOnlyAgentNotes($qb);
                }

                $qb->andWhere('t.organization = :organization')->setParameter('organization', $person->getOrganization());

                $qb->distinct(true);

                return $qb->getQuery()->getSingleScalarResult();
            }
        );
    }

    /**
     * Returns the $count number of most recent resolved tickets.
     *
     * @param int  $count
     * @param bool $ignoreOnlyNotes if true, we ignore tickets that only have agent messages (USE IN PORTAL)
     *
     * @return Ticket[]
     */
    public function getLatestResolvedTickets($count = 20, $ignoreOnlyNotes = true)
    {
        $em = $this->em;

        return $this->generateAndCache(
            [
                'getLatestResolvedTickets',
                $count,
                $ignoreOnlyNotes,
            ],
            function () use ($em, $count, $ignoreOnlyNotes) {
                $qb = $em->createQueryBuilder();

                $statusList = [Ticket::STATUS_RESOLVED];

                $qb->select('t')
                    ->from(Ticket::class, 't')
                    ->andWhere('t.status IN (:status_list)')->setParameter('status_list', $statusList);

                if ($ignoreOnlyNotes) {
                    $this->ignoreTicketsWithOnlyAgentNotes($qb);
                }

                $qb->setMaxResults($count);
                $qb->orderBy('t.date_resolved', 'DESC');

                return $qb->getQuery()->getResult();
            }
        );
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Ticket
     */
    protected function getTicketRepo()
    {
        return $this->em->getRepository(Ticket::class);
    }
}
