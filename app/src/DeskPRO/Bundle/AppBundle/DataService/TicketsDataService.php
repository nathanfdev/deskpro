<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class TicketsDataService extends AbstractDataService
{
    protected function ignoreTicketsWithOnlyAgentNotes(QueryBuilder $qb)
    {
        // make sure a message by the agent or the user has been made via using the last reply columns.
        $qb->andWhere('(t.date_last_agent_reply IS NOT NULL OR t.date_last_user_reply IS NOT NULL)');
    }

    /**
     * @param Person       $person
     * @param TicketFilter $filter
     * @param int          $page
     * @param int          $max_per_page
     * @param bool         $ignore_only_notes if true, we ignore tickets that only have agent messages (USE IN PORTAL)
     *
     * @return Pagerfanta
     */
    public function getPager(Person $person, TicketFilter $filter, $page, $max_per_page, $ignore_only_notes = true)
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getPager',
                $person,
                $filter,
                $page,
                $max_per_page,
                $ignore_only_notes,
            ),
            function () use ($em, $person, $filter, $page, $max_per_page, $ignore_only_notes) {
                $qb = $em->createQueryBuilder();

                $qb->select('t')
                    ->from('DeskPRO:Ticket', 't')
                    ->join('t.person', 'p')
                    ->where('t.status != :hidden')->setParameter('hidden', Ticket::STATUS_HIDDEN)
                ;

                if ($ignore_only_notes) {
                    $this->ignoreTicketsWithOnlyAgentNotes($qb);
                }

                // type
                if (TicketFilter::TYPE_OWN === $filter->getType()) {
                    if ($person->is_agent) {
                        // agents only their own tickets
                        $qb->andWhere('t.person = :person')->setParameter('person', $person);
                    } else {
                        if (!$person->organization || !$person->organization_manager) {
                            //  show non-agents the tickets they participate in
                            $qb->leftJoin('t.participants', 'part');
                            $qb->andWhere('t.person = :person OR part.person = :person')->setParameter('person', $person);
                        } else {
                            // but if they are an org manager, ignore the org tickets unless created directly by them (they show in org page, filtered below)
                            $qb->leftJoin('t.participants', 'part');
                            $qb->andWhere('t.person = :person OR (part.person = :person AND t.organization != :organization)');
                            $qb->setParameter('person', $person)->setParameter('organization', $person->organization);
                        }
                    }
                } else {
                    // its assumed that if you send in a person with an "organization" type filter that they have an
                    // organization and are a manger. ensure the controller/calling-code has this secured
                    $qb->andWhere('t.organization = :organization')->setParameter('organization', $person->organization);
                }

                // category
                switch ($filter->getCategory()) {

                    case TicketFilter::CATEGORY_AWAITING_AGENT:
                        $qb->andWhere('t.status = :status')->setParameter('status', Ticket::STATUS_AWAITING_AGENT);
                        break;

                    case TicketFilter::CATEGORY_RESOLVED:
                        $qb->andWhere('t.status = :status')->setParameter('status', Ticket::STATUS_RESOLVED);
                        break;

                    case TicketFilter::CATEGORY_AWAITING_USER:
                    default:
                        $qb->andWhere('t.status = :status')->setParameter('status', Ticket::STATUS_AWAITING_USER);
                        break;

                }

                // search query
                if ($q = $filter->getSearchQuery()) {
                    $qb->join('t.messages', 'm');
                    $qb->andWhere('(t.subject LIKE :query OR (m.message LIKE :query AND m.is_agent_note = 0))')->setParameter('query', '%'.$q.'%');
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
                        $qb->addOrderBy('t.person', $filter->getSortDirection());
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
                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * Returns the count of tickets that can be seen by the user by default. You can optionally provide a status to count on.
     *
     * @param Person $person
     * @param string $status            "open" [awaiting user or agent], "all" [open + resolved], or a specific status
     * @param bool   $ignore_only_notes if true, we ignore tickets that only have agent messages (USE IN PORTAL)
     *
     * @return int|null
     */
    public function getTicketCount(Person $person, $status = 'all', $ignore_only_notes = true)
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getTicketCount',
                $person,
                $status,
                $ignore_only_notes,
            ),
            function () use ($em, $person, $status, $ignore_only_notes) {
                $qb = $em->createQueryBuilder();

                if ('open' === $status) {
                    $status_list = array(
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_AWAITING_USER,
                    );
                } elseif ('all' !== $status) {
                    $status_list = array($status);
                } else {
                    $status_list = array(
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_RESOLVED,
                        Ticket::STATUS_AWAITING_USER,
                    );
                }

                $qb->select($qb->expr()->countDistinct('t.id'))
                    ->from('DeskPRO:Ticket', 't')
                    ->andWhere('t.status IN (:status_list)')->setParameter('status_list', $status_list)
                ;

                if ($ignore_only_notes) {
                    $this->ignoreTicketsWithOnlyAgentNotes($qb);
                }

                if ($person->is_agent) {
                    $qb->andWhere('t.person = :person')->setParameter('person', $person);
                } else {
                    $qb->leftJoin('t.participants', 'part');
                    $qb->andWhere('t.person = :person OR part.person = :person')->setParameter('person', $person);
                }

                $qb->distinct(true);

                return $qb->getQuery()->getSingleScalarResult();
            }
        );
    }

    /**
     * Returns the count of tickets that can be seen by the organization manager (stuff that would show up
     * if they clicked "Switch to Organization" in the ticket list).
     *
     * @param Person $person
     * @param string $status            "open" [awaiting user or agent], "all" [open + resolved], or a specific status
     * @param bool   $ignore_only_notes if true, we ignore tickets that only have agent messages (USE IN PORTAL)
     *
     * @return int|null
     */
    public function getOrganizationTicketCount(Person $person, $status = 'all', $ignore_only_notes = true)
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getOrganizationTicketCount',
                $person,
                $status,
                $ignore_only_notes,
            ),
            function () use ($em, $person, $status, $ignore_only_notes) {
                $qb = $em->createQueryBuilder();

                if ('open' === $status) {
                    $status_list = array(
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_AWAITING_USER,
                    );
                } elseif ('all' !== $status) {
                    $status_list = array($status);
                } else {
                    $status_list = array(
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_RESOLVED,
                        Ticket::STATUS_AWAITING_USER,
                    );
                }

                $qb->select($qb->expr()->countDistinct('t.id'))
                    ->from('DeskPRO:Ticket', 't')
                    ->andWhere('t.status IN (:status_list)')->setParameter('status_list', $status_list);

                if ($ignore_only_notes) {
                    $this->ignoreTicketsWithOnlyAgentNotes($qb);
                }

                if ($person->organization && $person->organization_manager) {
                    $qb->andWhere('t.organization = :organization')->setParameter('organization', $person->organization);
                }

                $qb->distinct(true);

                return $qb->getQuery()->getSingleScalarResult();
            }
        );
    }

    /**
     * Returns the $count number of most recent resolved tickets.
     *
     * @param int  $count
     * @param bool $ignore_only_notes if true, we ignore tickets that only have agent messages (USE IN PORTAL)
     *
     * @return Ticket[]
     */
    public function getLatestResolvedTickets($count = 20, $ignore_only_notes = true)
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getLatestResolvedTickets',
                $count,
                $ignore_only_notes,
            ),
            function () use ($em, $count, $ignore_only_notes) {
                $qb = $em->createQueryBuilder();

                $status_list = [Ticket::STATUS_RESOLVED];

                $qb->select('t')
                    ->from('DeskPRO:Ticket', 't')
                    ->andWhere('t.status IN (:status_list)')->setParameter('status_list', $status_list);

                if ($ignore_only_notes) {
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
        return $this->em->getRepository('DeskPRO:Ticket');
    }
}
