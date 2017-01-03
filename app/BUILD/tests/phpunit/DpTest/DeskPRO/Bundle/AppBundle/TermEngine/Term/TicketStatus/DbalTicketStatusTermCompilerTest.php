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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\DbalTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketStatusTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_status');
    }

    public function testCompileIsAndNoHidden()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'status' => [Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED],
            ]
        );

        $this->assertWhere($query_part, 'ticket.status IN (:status)');
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileIsNotAndNoHidden()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [Ticket::STATUS_ARCHIVED, Ticket::STATUS_RESOLVED],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'status' => [Ticket::STATUS_ARCHIVED, Ticket::STATUS_RESOLVED],
            ]
        );

        $this->assertWhere($query_part, 'ticket.status NOT IN (:status)');
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileIsAHidden()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [Ticket::HIDDEN_STATUS_SPAM, Ticket::HIDDEN_STATUS_DELETED],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'status_hidden' => Ticket::STATUS_HIDDEN,
                'hidden_status' => [Ticket::HIDDEN_STATUS_SPAM, Ticket::HIDDEN_STATUS_DELETED],
            ]
        );

        $this->assertWhere($query_part, 'ticket.status = :status_hidden AND ticket.hidden_status IN (:hidden_status)');
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileIsNotAHidden()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [
                    Ticket::HIDDEN_STATUS_SPAM,
                    Ticket::HIDDEN_STATUS_DELETED,
                ],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'status_hidden' => Ticket::STATUS_HIDDEN,
                'hidden_status' => [
                    Ticket::HIDDEN_STATUS_SPAM,
                    Ticket::HIDDEN_STATUS_DELETED,
                ],
            ]
        );

        $this->assertWhere(
            $query_part,
            'ticket.status != :status_hidden OR (ticket.status = :status_hidden AND ticket.hidden_status NOT IN (:hidden_status))'
        );
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileIsWithHidden()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [
                    Ticket::STATUS_AWAITING_AGENT,
                    Ticket::STATUS_RESOLVED,
                    Ticket::HIDDEN_STATUS_SPAM,
                ],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'status'        => [Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED],
                'status_hidden' => Ticket::STATUS_HIDDEN,
                'hidden_status' => [Ticket::HIDDEN_STATUS_SPAM],
            ]
        );

        $this->assertWhere(
            $query_part,
            'ticket.status IN (:status) OR (ticket.status = :status_hidden AND ticket.hidden_status IN (:hidden_status))'
        );
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileIsNOTWithHidden()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [
                    Ticket::STATUS_AWAITING_AGENT,
                    Ticket::STATUS_RESOLVED,
                    Ticket::HIDDEN_STATUS_SPAM,
                ],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'status'        => [Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED],
                'status_hidden' => Ticket::STATUS_HIDDEN,
                'hidden_status' => [Ticket::HIDDEN_STATUS_SPAM],
            ]
        );

        $this->assertWhere(
            $query_part,
            'ticket.status NOT IN (:status) OR (ticket.status = :status_hidden AND ticket.hidden_status NOT IN (:hidden_status))'
        );
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testThatHiddensWorkWithVerboseNames()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [
                    'hidden.'.Ticket::HIDDEN_STATUS_SPAM,
                    'hidden.'.Ticket::HIDDEN_STATUS_DELETED,
                ],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'status_hidden' => Ticket::STATUS_HIDDEN,
                'hidden_status' => [Ticket::HIDDEN_STATUS_SPAM, Ticket::HIDDEN_STATUS_DELETED],
            ]
        );

        $this->assertWhere($query_part, 'ticket.status = :status_hidden AND ticket.hidden_status IN (:hidden_status)');
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
