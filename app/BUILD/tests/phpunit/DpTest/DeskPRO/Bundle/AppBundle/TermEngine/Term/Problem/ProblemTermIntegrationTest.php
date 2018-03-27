<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem\ProblemTerm;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\TermIntegrationTest;

/**
 * Class ProblemTermIntegrationTest.
 */
class ProblemTermIntegrationTest extends TermIntegrationTest
{
    private $firstProblem;
    private $secondProblem;
    private $tickets;
    private $firstProblemTickets;
    private $secondProblemTickets;

    protected function setUp()
    {
        parent::setUp();

        if ($this->firstProblem) {
            return;
        }

        // given: 3 problems, 5 tickets

        $this->em->persist($this->firstProblem = $this->dummyProblem());
        $this->em->persist($this->secondProblem = $this->dummyProblem());
        $this->em->persist($thirdProblem = $this->dummyProblem());
        $this->tickets = [
            $this->dummyTicket(['problems' => [$this->firstProblem, $this->secondProblem, $thirdProblem]]),  // 0
            $this->dummyTicket(['problems' => [$this->secondProblem]]),                                      // 1
            $this->dummyTicket(['problems' => [$this->firstProblem]]),                                       // 2
            $this->dummyTicket(['problems' => [$this->secondProblem, $thirdProblem]]),                       // 3
            $this->dummyTicket(['problems' => [$this->firstProblem, $thirdProblem]]),                        // 4
        ];
        foreach ($this->tickets as $ticket) {
            $this->em->persist($ticket);
        }
        $this->em->flush();

        $this->firstProblemTickets  = [$this->tickets[0], $this->tickets[2], $this->tickets[4]];
        $this->secondProblemTickets = [$this->tickets[0], $this->tickets[1], $this->tickets[3]];
    }

    /**
     * @test Dbal
     */
    public function it_should_properly_select_tickets_with_the_first_problem()
    {
        $term = new ProblemTerm(['problem' => $this->firstProblem->getId()]);
        $this->assertTermSelectsTickets($term, $this->firstProblemTickets);
    }

    /**
     * @test Dbal
     */
    public function it_should_properly_select_tickets_with_the_second_problem()
    {
        $term = new ProblemTerm(['problem' => $this->secondProblem->getId()]);
        $this->assertTermSelectsTickets($term, $this->secondProblemTickets);
    }

    /**
     * @test Php checker (true)
     */
    public function it_should_properly_determine_that_a_ticket_is_associated_with_a_problem()
    {
        $term   = new ProblemTerm(['problem' => $this->firstProblem->getId()]);
        $ticket = $this->dummyTicket(['problems' => [$this->firstProblem]]);
        $this->assertTermSatisfies($term, $ticket);
    }

    /**
     * @test Php checker (false)
     */
    public function it_should_properly_determine_that_a_ticket_is_not_associated_with_a_problem()
    {
        $term   = new ProblemTerm(['problem' => $this->firstProblem->getId()]);
        $ticket = $this->dummyTicket(['problems' => [$this->secondProblem]]);
        $this->assertTermNotSatisfies($term, $ticket);
    }

    /**
     * @test TicketSelectCriteria
     */
    public function it_should_properly_select_first_problem_tickets_when_using_TicketSelectCriteria()
    {
        $term = $this->get('dp.app.term_engine.tickets_select_criteria')->createTerm(['problem' => $this->secondProblem->getId()]);
        $this->assertTermSelectsTickets($term, $this->secondProblemTickets);
    }
}
