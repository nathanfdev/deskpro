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

namespace DeskPRO\Bundle\PortalBundle\View\Ticket;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataService\TicketsDataService;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use Symfony\Component\HttpFoundation\Request;

class TicketListTable
{
    protected $ticket_type;
    protected $ticket_category;
    protected $ticket_filter;
    protected $page;
    protected $per_page;
    protected $pager;
    protected $page_name;
    protected $sort_name;
    protected $sort_direction_name;
    protected $title;

    public function __construct($ticket_category, $ticket_type, $title)
    {
        $this->ticket_category     = $ticket_category;
        $this->ticket_type         = $ticket_type;
        $this->title               = $title;
        $this->page_name           = $ticket_category.'_page';
        $this->sort_name           = $ticket_category.'_sort';
        $this->sort_direction_name = $ticket_category.'_sort_direction';
        $this->ticket_filter       = null;
        $this->pager               = null;
    }

    public function makeFilterWithRequest(Request $request, $per_page = 10)
    {
        $this->ticket_filter = new TicketFilter(
            $this->ticket_type,
            $this->ticket_category,
            $request->query->get($this->sort_name, 'activity'),
            $request->query->get($this->sort_direction_name, 'desc')
        );
        $this->page     = $request->query->get($this->page_name, 1);
        $this->per_page = $per_page;
    }

    public function makePagerUsingDataService(TicketsDataService $data_service, Person $person)
    {
        if (!$this->ticket_filter) {
            throw new \RuntimeException('TicketListTable::makrPagerUsingDataService requires that a filter be present');
        }

        $this->pager = $data_service->getPager($person, $this->ticket_filter, $this->page, $this->per_page);

        return $this->pager;
    }

    /**
     * @return mixed
     */
    public function getTicketType()
    {
        return $this->ticket_type;
    }

    /**
     * @return mixed
     */
    public function getTicketCategory()
    {
        return $this->ticket_category;
    }

    /**
     */
    public function getTicketFilter()
    {
        return $this->ticket_filter;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * @return string
     */
    public function getPageName()
    {
        return $this->page_name;
    }

    /**
     * @return string
     */
    public function getSortName()
    {
        return $this->sort_name;
    }

    /**
     * @return string
     */
    public function getSortDirectionName()
    {
        return $this->sort_direction_name;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}
