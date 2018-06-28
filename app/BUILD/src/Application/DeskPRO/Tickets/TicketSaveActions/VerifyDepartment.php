<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Departments\TicketDepartments;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Form\BrandFormHelper;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;

/**
 * Class VerifyDepartment.
 */
class VerifyDepartment implements TicketSaveActionInterface
{
    /**
     * @var TicketDepartments
     */
    private $ticketDeps;

    /**
     * @var BrandFormHelper
     */
    private $helper;

    /**
     * Constructor.
     *
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

        $this->checkSetDepartmentIsInvalid($ticket, $context);
        $this->checkDepartmentIsNotSet($ticket, $context);
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    private function checkSetDepartmentIsInvalid(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($ticket->getDepartment() && $this->ticketDeps->getChildren($ticket->getDepartment())) {
            $defaultDepartment = $this->getDefaultDepartment($ticket, $context);
            if (!$defaultDepartment) {
                return;
            }

            $set = $ticket->getDepartment();
            $context->getLogger()->info(
                sprintf(
                    'The set department %s ( %d ) has children. Reverting to default: %s ( %d )',
                    $set->getTitle(),
                    $set->getId(),
                    $defaultDepartment->getTitle(),
                    $defaultDepartment->getId()
                )
            );
            $ticket->setDepartment($defaultDepartment);
        }
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    private function checkDepartmentIsNotSet(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$ticket->getDepartment()) {
            $defaultDepartment = $this->getDefaultDepartment($ticket, $context);
            if (!$defaultDepartment) {
                return;
            }

            $context->getLogger()->info(
                sprintf(
                    'Setting default department: %s ( %d )',
                    $defaultDepartment->getId(),
                    $defaultDepartment->getTitle()
                )
            );
            $ticket->setDepartment($defaultDepartment);
        }
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return Department
     */
    private function getDefaultDepartment(Ticket $ticket, ExecutorContextInterface $context)
    {
        $type = $ticket->getPerson() && $ticket->getPerson()->isAgent()
            ? DefaultDepartmentSettings::DEFAULT_DEPARTMENT_AGENT_TYPE
            : DefaultDepartmentSettings::DEFAULT_DEPARTMENT_USER_TYPE;

        $defaultDepartment = $this->helper->getDefaultDepartment($type, $ticket->getBrand());

        if (!$defaultDepartment || $this->ticketDeps->getChildren($defaultDepartment)) {
            $defaultDepartment = $this->ticketDeps->getDefaultDepartment($ticket->getBrand());
            if ($defaultDepartment) {
                $context->getLogger()->info(
                    sprintf(
                        'Default department for brand not found or invalid. Picking system default: %s ( %d )',
                        $defaultDepartment->getTitle(),
                        $defaultDepartment->getId()
                    )
                );
            }
        }

        return $defaultDepartment;
    }
}
