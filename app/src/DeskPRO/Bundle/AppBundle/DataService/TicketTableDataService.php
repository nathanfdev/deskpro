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
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Model\TicketColumn;
use DeskPRO\Bundle\AppBundle\Model\TicketColumns;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable;
use Symfony\Component\HttpFoundation\Request;

class TicketTableDataService extends AbstractDataService
{
    /**
     * @var TicketsDataService
     */
    private $ticket_data_service;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    public function __construct(EntityManager $em, TicketsDataService $ticket_data_service, BrandStack $brand_stack, LanguageManager $language_manager)
    {
        parent::__construct($em);
        $this->ticket_data_service = $ticket_data_service;
        $this->brand_stack         = $brand_stack;
        $this->language_manager    = $language_manager;
    }

    public function makeTicketTable(Person $person, Request $request, $ticket_type, $category, $category_title)
    {
        $columns  = $this->makeColumnControl($person);
        $per_page = $this->brand_stack->getActive()->getSetting('portal.per_page_tickets', 10);
        $table    = new TicketListTable($category, $ticket_type, $category_title, $columns, $request, $per_page);
        $table->makePagerUsingDataService($this->ticket_data_service, $person);

        return $table;
    }

    public function makeColumnControl(Person $person)
    {
        $column_control = new TicketColumns();

        $column_control->addColumn(
            TicketColumn::TYPE_DEPARTMENT_SUBJECT,
            $this->phrase('portal.tickets.list_department').' / '.$this->phrase('portal.tickets.list_subject'),
            TicketColumn::TYPE_DEPARTMENT_SUBJECT
        );

        $column_control->addColumn(
            TicketColumn::TYPE_AGENT,
            $this->phrase('portal.tickets.list_agent'),
            TicketColumn::TYPE_AGENT
        );

        $column_control->addColumn(
            TicketColumn::TYPE_DATE_CREATED,
            $this->phrase('portal.tickets.list_date_created'),
            TicketColumn::TYPE_DATE_CREATED
        );

        $column_control->addColumn(
            TicketColumn::TYPE_DATE_ACTIVITY,
            $this->phrase('portal.tickets.list_last_action'),
            TicketColumn::TYPE_DATE_ACTIVITY
        );

        $column_control->addColumn(
            TicketColumn::TYPE_DATE_USER,
            $this->phrase('portal.tickets.list_date_last_user'),
            TicketColumn::TYPE_DATE_USER
        );

        $column_control->addColumn(
            TicketColumn::TYPE_DATE_AGENT,
            $this->phrase('portal.tickets.list_date_last_agent'),
            TicketColumn::TYPE_DATE_AGENT
        );

        return $column_control;
    }

    protected function phrase($phrase_name, $vars = [])
    {
        return $this->language_manager->phrase($phrase_name, $vars);
    }
}
