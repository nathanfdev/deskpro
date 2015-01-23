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
use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\Translate\Translate;
use Application\PortalBundle\Model\TicketFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
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
        // TODO: this needs to be a full blown search with filters
        $tickets = new ArrayCollection($this->getTicketRepo()->findAwaitingAgentTicketsForPerson($person));

        // TODO: make sure this collection adapter gets a collection that is EXTRA_LAZY!
        $pager = new Pagerfanta(new DoctrineCollectionAdapter($tickets));
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
