<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmailTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatusTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalTicketStatusTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    public function testCompileIsAndNoHidden()
    {
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED)
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.status IN (:status_0)',
            array(
                'status_0' => array(Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED)
            )
        );
    }

    public function testCompileIsNotAndNoHidden()
    {
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::STATUS_ARCHIVED, Ticket::STATUS_RESOLVED)
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.status NOT IN (:status_0)',
            array(
                'status_0' => array(Ticket::STATUS_ARCHIVED, Ticket::STATUS_RESOLVED)
            )
        );
    }

    public function testCompileIsAHidden()
    {
        $term = new TicketStatusTerm(
            array(
                'status' => array(Ticket::HIDDEN_STATUS_SPAM, Ticket::HIDDEN_STATUS_DELETED)
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.status = :status_0 AND ticket.hidden_status IN (:status_1)',
            array(
                'status_0' => Ticket::STATUS_HIDDEN,
                'status_1' => array(Ticket::HIDDEN_STATUS_SPAM, Ticket::HIDDEN_STATUS_DELETED)
            )
        );
    }

    public function testCompileIsNotAHidden()
    {
        $term = new TicketStatusTerm(
            array(
                'status' => array(
                    Ticket::HIDDEN_STATUS_SPAM,
                    Ticket::HIDDEN_STATUS_DELETED,
                    Ticket::HIDDEN_STATUS_VALIDATING
                )
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.status != :status_0 OR (ticket.status = :status_0 AND ticket.hidden_status NOT IN (:status_1))',
            array(
                'status_0' => Ticket::STATUS_HIDDEN,
                'status_1' => array(
                    Ticket::HIDDEN_STATUS_SPAM,
                    Ticket::HIDDEN_STATUS_DELETED,
                    Ticket::HIDDEN_STATUS_VALIDATING
                )
            )
        );
    }

    public function testCompileIsWithHidden()
    {
        $term = new TicketStatusTerm(
            array(
                'status' => array(
                    Ticket::STATUS_AWAITING_AGENT,
                    Ticket::STATUS_RESOLVED,
                    Ticket::HIDDEN_STATUS_SPAM
                )
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.status IN (:status_0) OR (ticket.status = :status_1 AND ticket.hidden_status IN (:status_2))',
            array(
                'status_0' => array(Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED),
                'status_1' => Ticket::STATUS_HIDDEN,
                'status_2' => array(Ticket::HIDDEN_STATUS_SPAM)
            )
        );
    }

    public function testCompileIsNOTWithHidden()
    {
        $term = new TicketStatusTerm(
            array(
                'status' => array(
                    Ticket::STATUS_AWAITING_AGENT,
                    Ticket::STATUS_RESOLVED,
                    Ticket::HIDDEN_STATUS_SPAM
                )
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.status NOT IN (:status_0) OR (ticket.status = :status_1 AND ticket.hidden_status NOT IN (:status_2))',
            array(
                'status_0' => array(Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED),
                'status_1' => Ticket::STATUS_HIDDEN,
                'status_2' => array(Ticket::HIDDEN_STATUS_SPAM)
            )
        );
    }
}
