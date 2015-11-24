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
namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketsSelectCriteria;
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
        $term = TicketsSelectCriteria::createTerm(['person' => $this->carol->getId()]);
        $this->assertTermSelectsTickets($term, $this->carolTickets);
    }
}
