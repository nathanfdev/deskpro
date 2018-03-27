<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData\CustomDataTerm;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\TermIntegrationTest;

class CustomDataTermIntegrationTest extends TermIntegrationTest
{
    /**
     * @var Ticket[]
     */
    private static $tickets = [];
    private static $fieldType1;
    private static $fieldType2;
    private static $ticketsWithField1ValueEqualToFoo;
    private static $ticketsWithField1ValueEqualToBar;
    private static $ticketsWithField2ValueEqualTo42;

    protected function setUp()
    {
        parent::setUp();
        self::$tickets = [];

        $this->em->persist(self::$fieldType1 = $this->dummyCustomDefTicket());
        $this->em->persist(self::$fieldType2 = $this->dummyCustomDefTicket());

        for ($i = 0; $i < 5; ++$i) {
            $ticket = $this->dummyTicket();
            $this->em->persist($ticket);
            self::$tickets[] = $ticket;
        }

        $this->em->flush();

        $this->em->persist($this->dummyCustomDataTicket(self::$tickets[0], self::$fieldType1, 'input', 'foo'));
        $this->em->persist($this->dummyCustomDataTicket(self::$tickets[1], self::$fieldType1, 'input', 'bar'));
        $this->em->persist($this->dummyCustomDataTicket(self::$tickets[2], self::$fieldType1, 'input', 'foo'));
        $this->em->persist($this->dummyCustomDataTicket(self::$tickets[3], self::$fieldType1, 'input', 'foo'));

        $this->em->persist($this->dummyCustomDataTicket(self::$tickets[0], self::$fieldType2, 'value', 42));
        $this->em->persist($this->dummyCustomDataTicket(self::$tickets[1], self::$fieldType2, 'value', 13));
        $this->em->persist($this->dummyCustomDataTicket(self::$tickets[4], self::$fieldType2, 'value', 42));

        $this->em->flush();

        self::$ticketsWithField1ValueEqualToFoo = [self::$tickets[0], self::$tickets[2], self::$tickets[3]];
        self::$ticketsWithField1ValueEqualToBar = [self::$tickets[1]];
        self::$ticketsWithField2ValueEqualTo42  = [self::$tickets[0], self::$tickets[4]];
    }

    /**
     * @test Dbal (input)
     */
    public function it_should_properly_select_tickets_with_first_field_equal_to_foo()
    {
        $term = new CustomDataTerm(['field_id' => self::$fieldType1->getId(), 'custom_data_value' => 'foo']);
        $this->assertTermSelectsTickets($term, self::$ticketsWithField1ValueEqualToFoo);
    }

    /**
     * @test Dbal (input)
     */
    public function it_should_properly_select_tickets_with_first_custom_field_equal_to_bar()
    {
        $term = new CustomDataTerm(['field_id' => self::$fieldType1->getId(), 'custom_data_value' => 'bar']);
        $this->assertTermSelectsTickets($term, self::$ticketsWithField1ValueEqualToBar);
    }

    /**
     * @test Dbal (value)
     */
    public function it_should_properly_select_tickets_with_second_custom_field_equal_to_42()
    {
        $term = new CustomDataTerm(['field_id' => self::$fieldType2->getId(), 'custom_data_value' => 42]);
        $this->assertTermSelectsTickets($term, self::$ticketsWithField2ValueEqualTo42);
    }

    /**
     * @test Php checker (input, true)
     */
    public function it_should_properly_determine_that_first_custom_field_is_set_to_foo()
    {
        $field_id = self::$fieldType1->getId();
        $term     = new CustomDataTerm(['field_id' => $field_id, 'custom_data_value' => 'foo']);
        $ticket   = $this->dummyTicket();
        $ticket->setCustomDataField($field_id, 'input', 'foo');
        $this->assertTermSatisfies($term, $ticket);
    }

    /**
     * @test Php checker (value, true)
     */
    public function it_should_properly_determine_that_second_custom_field_is_set_to_42()
    {
        $field_id = self::$fieldType2->getId();
        $term     = new CustomDataTerm(['field_id' => $field_id, 'custom_data_value' => 42]);
        $ticket   = $this->dummyTicket();
        $ticket->setCustomDataField($field_id, 'value', 42);
        $this->assertTermSatisfies($term, $ticket);
    }

    /**
     * @test Php checker (input, false)
     */
    public function it_should_properly_determine_that_first_custom_field_is_not_set_to_foo()
    {
        $field_id = self::$fieldType1->getId();
        $term     = new CustomDataTerm(['field_id' => $field_id, 'custom_data_value' => 'foo']);
        $ticket   = $this->dummyTicket();
        $ticket->setCustomDataField($field_id, 'input', 'bar');
        $this->assertTermNotSatisfies($term, $ticket);
    }

    /**
     * @test TicketSelectCriteria
     */
    public function it_should_select_tickets_with_first_custom_field_equal_to_foo_when_using_TicketSelectCriteria()
    {
        $field_name = 'ticket_field_'.self::$fieldType1->getId();
        $term       = $this->get('dp.app.term_engine.tickets_select_criteria')->createTerm([$field_name => 'foo']);
        $this->assertTermSelectsTickets($term, self::$ticketsWithField1ValueEqualToFoo);
    }
}
