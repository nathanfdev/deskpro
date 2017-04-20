<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
