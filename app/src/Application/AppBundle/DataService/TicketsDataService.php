<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace Application\AppBundle\DataService;


use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\Translate\Translate;
use Application\PortalBundle\Model\TicketFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class TicketsDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Person $person
     * @param TicketFilter $filter
     * @param $page
     * @param $max_per_page
     * @return Pagerfanta
     */
    public function getPager(Person $person, TicketFilter $filter, $page, $max_per_page)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('t')
            ->from('DeskPRO:Ticket', 't')
            ->join('t.person', 'p')
        ;

        // TODO: fix this for both types
        // type
        if (TicketFilter::TYPE_OWN === $filter->getType()) {
            // a person own ticket cannot be for an organization
            $qb->andWhere('t.person = :person AND t.organization IS NULL')->setParameter('person', $person);
        } else {
            $qb->join('t.organization', 'o');
            // where person is in organization
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

        // sort
        switch ($filter->getSort()) {

            // TODO: last activity algorithm (same as Ticket::getLastActivityDate())
            case TicketFilter::SORT_ACTIVITY:
                $sort_string = 't.date_last_agent_reply';
                break;

            case TicketFilter::SORT_CREATED:
            default:
                $sort_string = 't.date_created';
        }

        $qb->orderBy($sort_string, $filter->getSortDirection());

        //print($qb->getQuery()->getDQL());exit;

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($max_per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Ticket
     */
    protected function getTicketRepo()
    {
        return $this->em->getRepository('DeskPRO:Ticket');
    }
}
