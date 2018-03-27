<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateCreated;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketDateCreatedTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateCreated\TicketDateCreatedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketDateCreatedTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketDateCreatedTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_date_created');
    }

    public function testSimpleCases()
    {
        $date  = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $date2 = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $check1 = clone $date;
        $check1->setTimezone(new \DateTimeZone('UTC'));
        $check1 = $check1->format('Y-m-d H:i:s');
        $check2 = clone $date2;
        $check2->setTimezone(new \DateTimeZone('UTC'));
        $check2 = $check2->format('Y-m-d H:i:s');

        $ops = [
            TermInterface::OP_IS        => 'ticket.date_created = :date',
            TermInterface::OP_NOT       => 'ticket.date_created != :date',
            TermInterface::OP_GT        => 'ticket.date_created > :date',
            TermInterface::OP_LT        => 'ticket.date_created < :date',
            TermInterface::OP_GTE       => 'ticket.date_created >= :date',
            TermInterface::OP_LTE       => 'ticket.date_created <= :date',
            TermInterface::OP_RANGE     => 'ticket.date_created BETWEEN :date AND :date2',
            TermInterface::OP_NOT_RANGE => 'ticket.date_created NOT BETWEEN :date AND :date2',
        ];

        foreach ($ops as $op => $where) {
            if (TermInterface::OP_RANGE === $op || TermInterface::OP_NOT_RANGE === $op) {
                $term       = new TicketDateCreatedTerm(['date' => $date, 'date2' => $date2], $op);
                $query_part = $this->term_compiler->compile($term);
                $this->assertParameters($query_part, ['date' => $check1, 'date2' => $check2]);
            } else {
                $term       = new TicketDateCreatedTerm(['date' => $date], $op);
                $query_part = $this->term_compiler->compile($term);
                $this->assertParameters($query_part, ['date' => $check1]);
            }
            $this->assertWhere($query_part, $where);
            $this->assertNoJoins($query_part);
            $this->assertNoUniqueJoins($query_part);
        }
    }

    public function testSingleDateIntoRange()
    {
        $date = new \DateTime('now', new \DateTimeZone('Europe/London'));

        $check1 = new \DateTime('00:00:00', new \DateTimeZone('Europe/London'));
        $check1->setTimezone(new \DateTimeZone('UTC'));
        $check1 = $check1->format('Y-m-d H:i:s');
        $check2 = new \DateTime('23:59:59', new \DateTimeZone('Europe/London'));
        $check2->setTimezone(new \DateTimeZone('UTC'));
        $check2 = $check2->format('Y-m-d H:i:s');

        $ops = [
            TermInterface::OP_IS        => 'ticket.date_created BETWEEN :date AND :date2',
            TermInterface::OP_NOT       => 'ticket.date_created NOT BETWEEN :date AND :date2',
            TermInterface::OP_GT        => 'ticket.date_created > :date',
            TermInterface::OP_LT        => 'ticket.date_created < :date',
            TermInterface::OP_GTE       => 'ticket.date_created >= :date',
            TermInterface::OP_LTE       => 'ticket.date_created <= :date',
            TermInterface::OP_RANGE     => 'ticket.date_created BETWEEN :date AND :date2',
            TermInterface::OP_NOT_RANGE => 'ticket.date_created NOT BETWEEN :date AND :date2',
        ];

        foreach ($ops as $op => $where) {
            $term       = new TicketDateCreatedTerm(['date' => $date, 'ignore_time' => true], $op);
            $query_part = $this->term_compiler->compile($term);

            if (in_array($op, [TermInterface::OP_RANGE, TermInterface::OP_NOT_RANGE, TermInterface::OP_IS, TermInterface::OP_NOT])) {
                $this->assertParameters($query_part, ['date' => $check1, 'date2' => $check2]);
            } else {
                $this->assertParameters($query_part, ['date' => $check1]);
            }

            $this->assertWhere($query_part, $where);
            $this->assertNoJoins($query_part);
            $this->assertNoUniqueJoins($query_part);
        }
    }
}
