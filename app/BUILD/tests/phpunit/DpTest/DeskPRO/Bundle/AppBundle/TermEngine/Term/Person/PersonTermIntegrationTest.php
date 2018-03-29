<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PersonTerm;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\TermIntegrationTest;

class PersonTermIntegrationTest extends TermIntegrationTest
{
    private $bob;
    private $alice;
    private $carol;
    private $tickets;
    private $bobTickets;
    private $aliceTickets;
    private $carolTickets;

    protected function setUp()
    {
        parent::setUp();

        if ($this->bob) {
            return;
        }

        // given: 3 person, 6 tickets

        $this->em->persist($this->bob = $this->dummyPerson());
        $this->em->persist($this->alice = $this->dummyPerson());
        $this->em->persist($this->carol = $this->dummyPerson());
        $this->tickets = [
            $this->dummyTicket(['person' => $this->bob]),   // 0
            $this->dummyTicket(['person' => $this->alice]), // 1
            $this->dummyTicket(['person' => $this->bob]),   // 2
            $this->dummyTicket(['person' => $this->alice]), // 3
            $this->dummyTicket(['person' => $this->bob]),   // 4
            $this->dummyTicket(['person' => $this->carol]), // 5
        ];
        foreach ($this->tickets as $ticket) {
            $this->em->persist($ticket);
        }
        $this->em->flush();

        $this->bobTickets   = [$this->tickets[0], $this->tickets[2], $this->tickets[4]];
        $this->aliceTickets = [$this->tickets[1], $this->tickets[3]];
        $this->carolTickets = [$this->tickets[5]];
    }

    /**
     * @test Dbal
     */
    public function it_should_properly_select_tickets_of_Bob()
    {
        $term = new PersonTerm(['person_ids' => [$this->bob->getId()]]);
        $this->assertTermSelectsTickets($term, $this->bobTickets);
    }

    /**
     * @test Dbal
     */
    public function it_should_properly_select_tickets_of_Bob_and_Alice()
    {
        $term = new PersonTerm(['person_ids' => [$this->bob->getId(), $this->alice->getId()]]);
        $this->assertTermSelectsTickets($term, array_merge($this->bobTickets, $this->aliceTickets));
    }

    /**
     * @test Php checker (true)
     */
    public function it_should_properly_determine_that_a_ticket_belongs_to_a_person()
    {
        $term   = new PersonTerm(['person_ids' => [$this->bob->getId()]]);
        $ticket = $this->dummyTicket(['person' => $this->bob]);
        $this->assertTermSatisfies($term, $ticket);
    }

    /**
     * @test Php checker (false)
     */
    public function it_should_properly_determine_that_a_ticket_does_not_belong_to_a_person()
    {
        $term   = new PersonTerm(['person_ids' => [$this->bob->getId()]]);
        $ticket = $this->dummyTicket(['person' => $this->alice]);
        $this->assertTermNotSatisfies($term, $ticket);
    }

    /**
     * @test TicketSelectCriteria
     */
    public function it_should_properly_select_Carol_tickets_when_using_TicketSelectCriteria()
    {
        $term = $this->get('dp.app.term_engine.tickets_select_criteria')->createTerm(['person' => $this->carol->getId()]);
        $this->assertTermSelectsTickets($term, $this->carolTickets);
    }
}
