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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateLastUserReply;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketDateLastUserReplyTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateLastUserReply\TicketDateLastUserReplyTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketDateLastUserReplyTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketDateLastUserReplyTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_date_last_user_reply');
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
            TermInterface::OP_IS        => 'ticket.date_last_user_reply = :date',
            TermInterface::OP_NOT       => 'ticket.date_last_user_reply != :date',
            TermInterface::OP_GT        => 'ticket.date_last_user_reply > :date',
            TermInterface::OP_LT        => 'ticket.date_last_user_reply < :date',
            TermInterface::OP_GTE       => 'ticket.date_last_user_reply >= :date',
            TermInterface::OP_LTE       => 'ticket.date_last_user_reply <= :date',
            TermInterface::OP_RANGE     => 'ticket.date_last_user_reply BETWEEN :date AND :date2',
            TermInterface::OP_NOT_RANGE => 'ticket.date_last_user_reply NOT BETWEEN :date AND :date2',
        ];

        foreach ($ops as $op => $where) {
            if (TermInterface::OP_RANGE === $op || TermInterface::OP_NOT_RANGE === $op) {
                $term       = new TicketDateLastUserReplyTerm(['date' => $date, 'date2' => $date2], $op);
                $query_part = $this->term_compiler->compile($term);
                $this->assertParameters($query_part, ['date' => $check1, 'date2' => $check2]);
            } else {
                $term       = new TicketDateLastUserReplyTerm(['date' => $date], $op);
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
            TermInterface::OP_IS        => 'ticket.date_last_user_reply BETWEEN :date AND :date2',
            TermInterface::OP_NOT       => 'ticket.date_last_user_reply NOT BETWEEN :date AND :date2',
            TermInterface::OP_GT        => 'ticket.date_last_user_reply > :date',
            TermInterface::OP_LT        => 'ticket.date_last_user_reply < :date',
            TermInterface::OP_GTE       => 'ticket.date_last_user_reply >= :date',
            TermInterface::OP_LTE       => 'ticket.date_last_user_reply <= :date',
            TermInterface::OP_RANGE     => 'ticket.date_last_user_reply BETWEEN :date AND :date2',
            TermInterface::OP_NOT_RANGE => 'ticket.date_last_user_reply NOT BETWEEN :date AND :date2',
        ];

        foreach ($ops as $op => $where) {
            $term       = new TicketDateLastUserReplyTerm(['date' => $date, 'ignore_time' => true], $op);
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
