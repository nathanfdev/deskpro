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
