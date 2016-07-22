<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Departments\TicketDepartments;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Form\BrandFormHelper;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;

class VerifyDepartment implements TicketSaveActionInterface
{
    /**
     * @var TicketDepartments
     */
    private $ticketDeps;

    private $helper;

    /**
     * @param TicketDepartments $ticketDeps
     * @param BrandFormHelper   $helper
     */
    public function __construct(TicketDepartments $ticketDeps, BrandFormHelper $helper)
    {
        $this->ticketDeps = $ticketDeps;
        $this->helper     = $helper;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop') {
            return;
        }

        if (!$ticket->department) {
            $type = $ticket->getPerson() && $ticket->getPerson()->isAgent()
                ? DefaultDepartmentSettings::DEFAULT_DEPARTMENT_AGENT_TYPE
                : DefaultDepartmentSettings::DEFAULT_DEPARTMENT_USER_TYPE;
            $ticket->setDepartment($this->helper->getDefaultDepartment($type));
        }

        if (!$ticket->department) {
            if ($dep = $this->ticketDeps->getDefaultDepartment()) {
                $context->getLogger()->info("Setting system default department: {$dep->getId()} {$dep->getTitle()}");
                $ticket->department = $dep;
            }
        }

        if ($ticket->department && $this->ticketDeps->getChildren($ticket->department)) {
            $set = $ticket->department;
            $dep = $this->ticketDeps->getDefaultDepartment();
            $context->getLogger()->info("The set department {$set->id} {$set->title} has children. Reverting to system default: {$dep->id} {$dep->title}");
            $ticket->department = $dep;
        }
    }
}
