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


use Application\AppBundle\Helper\ArbitratyHasher;
use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\Translate\Translate;
use Application\PortalBundle\Model\TicketFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class TicketsDataService extends AbstractDataService
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
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getPager',
                $person,
                $filter,
                $page,
                $max_per_page
            ),
            function() use($em, $person, $filter, $page, $max_per_page) {
                $qb = $em->createQueryBuilder();

                $qb->select('t')
                    ->from('DeskPRO:Ticket', 't')
                    ->join('t.person', 'p')
                    ->where('t.status != :hidden')->setParameter('hidden', Ticket::STATUS_HIDDEN);

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

                // sort
                switch ($filter->getSort()) {

                    // TODO: last activity algorithm (same as Ticket::getLastActivityDate())
                    case TicketFilter::SORT_ACTIVITY:
                        $qb->addOrderBy('t.date_last_user_reply', $filter->getSortDirection());
                        $qb->addOrderBy('t.date_last_agent_reply', $filter->getSortDirection());
                        $qb->addOrderBy('t.date_created', $filter->getSortDirection());
                        break;

                    case TicketFilter::SORT_DEPARTMENT:
                        $qb->join('t.department', 'd');
                        $qb->orderBy('d.title', $filter->getSortDirection());
                        break;

                    case TicketFilter::SORT_CREATED:
                    default:
                        $qb->orderBy('t.date_created', $filter->getSortDirection());
                }

                $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            });
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Ticket
     */
    protected function getTicketRepo()
    {
        return $this->em->getRepository('DeskPRO:Ticket');
    }
}
