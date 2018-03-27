<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization\OrganizationTerm;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\TermIntegrationTest;

class OrganizationTermIntegrationTest extends TermIntegrationTest
{
    private $google;
    private $yahoo;
    private $tickets;
    private $googleTickets;
    private $yahooTickets;

    protected function setUp()
    {
        parent::setUp();

        if ($this->google) {
            return;
        }

        // given: 2 organization, 5 tickets

        $this->em->persist($this->google = $this->dummyOrganization());
        $this->em->persist($this->yahoo = $this->dummyOrganization());
        $this->tickets = [
            $this->dummyTicket(['organization' => $this->google]), // 0
            $this->dummyTicket(['organization' => $this->yahoo]),  // 1
            $this->dummyTicket(['organization' => $this->google]), // 2
            $this->dummyTicket(['organization' => $this->yahoo]),  // 3
            $this->dummyTicket(['organization' => $this->google]), // 4
        ];
        foreach ($this->tickets as $ticket) {
            $this->em->persist($ticket);
        }
        $this->em->flush();

        $this->googleTickets = [$this->tickets[0], $this->tickets[2], $this->tickets[4]];
        $this->yahooTickets  = [$this->tickets[1], $this->tickets[3]];
    }

    /**
     * @test Dbal
     */
    public function it_should_properly_select_tickets_of_Google_organization()
    {
        $term = new OrganizationTerm(['organization' => $this->google->getId()]);
        $this->assertTermSelectsTickets($term, $this->googleTickets);
    }

    /**
     * @test Dbal
     */
    public function it_should_properly_select_tickets_of_Yahoo_organization()
    {
        $term = new OrganizationTerm(['organization' => $this->yahoo->getId()]);
        $this->assertTermSelectsTickets($term, $this->yahooTickets);
    }

    /**
     * @test Php checker (true)
     */
    public function it_should_properly_determine_that_a_ticket_belongs_to_a_organization()
    {
        $term   = new OrganizationTerm(['organization' => $this->google->getId()]);
        $ticket = $this->dummyTicket(['organization' => $this->google]);
        $this->assertTermSatisfies($term, $ticket);
    }

    /**
     * @test Php checker (false)
     */
    public function it_should_properly_determine_that_a_ticket_does_not_belong_to_a_organization()
    {
        $term   = new OrganizationTerm(['organization' => $this->google->getId()]);
        $ticket = $this->dummyTicket(['organization' => $this->yahoo]);
        $this->assertTermNotSatisfies($term, $ticket);
    }

    /**
     * @test TicketSelectCriteria
     */
    public function it_should_properly_select_Yahoo_tickets_when_using_TicketSelectCriteria()
    {
        $term = $this->get('dp.app.term_engine.tickets_select_criteria')->createTerm(['organization' => $this->yahoo->getId()]);
        $this->assertTermSelectsTickets($term, $this->yahooTickets);
    }
}
