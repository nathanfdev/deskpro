<?php

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
