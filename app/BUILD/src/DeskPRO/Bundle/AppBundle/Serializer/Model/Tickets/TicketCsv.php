<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\Ticket as TicketEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketCsv.
 *
 * @JMS\ExclusionPolicy("none")
 */
class TicketCsv extends Ticket
{
    /**
     * Name af assigned agent.
     *
     * @JMS\Type("string")
     */
    private $agent;

    /**
     * Name af assigned agent team.
     *
     * @JMS\Type("string")
     */
    private $agentTeam;

    /**
     * Name af assigned person.
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * Workflow.
     *
     * @JMS\Type("string")
     */
    private $workflow;

    /**
     * Language.
     *
     * @JMS\Type("string")
     */
    private $language;

    /**
     * Department.
     *
     * @JMS\Type("string")
     */
    private $department;

    /**
     * Organization.
     *
     * @JMS\Type("string")
     */
    private $organization;

    /**
     * Department.
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * Product.
     *
     * @JMS\Type("string")
     */
    private $product;

    /**
     * Constructor.
     *
     * @param TicketEntity $ticket
     */
    public function __construct(TicketEntity $ticket)
    {
        parent::__construct($ticket);
        $this->agent        = $ticket->getAgent() ? $ticket->getAgent()->getName() : '';
        $this->agentTeam    = $ticket->getAgentTeam() ? $ticket->getAgentTeam()->getName() : '';
        $this->person       = $ticket->getPerson() ? $ticket->getPerson()->getName() : '';
        $this->workflow     = $ticket->getWorkflow() ? $ticket->getWorkflow()->getTitle() : '';
        $this->organization = $ticket->getOrganization() ? $ticket->getOrganization()->getName() : '';
        $this->language     = $ticket->getLanguage() ? $ticket->getLanguage()->getTitle() : '';
        $this->department   = $ticket->getDepartment() ? $ticket->getDepartment()->getTitle() : '';
        $this->category     = $ticket->getCategory() ? $ticket->getCategory()->getTitle() : '';
        $this->product      = $ticket->getProduct() ? $ticket->getProduct()->getTitle() : '';
    }
}
