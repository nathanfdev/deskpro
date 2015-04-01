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

use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipantTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalTicketParticipantTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    public function testCompileIs()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array(4, 9)
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'tickets_participants_0.person_id IN (:person_ids_0)',
            array(
                'person_ids_0' => array(4, 9),
            ),
            null,
            'LEFT JOIN tickets_participants tickets_participants_0 ON (tickets_participants_0.ticket_id = ticket.id)'
        );
    }

    public function testCompileIsNOT()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array(14)
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'tickets_participants_0.person_id NOT IN (:person_ids_0)',
            array(
                'person_ids_0' => array(14),
            ),
            null,
            'LEFT JOIN tickets_participants tickets_participants_0 ON (tickets_participants_0.ticket_id = ticket.id)'
        );
    }

    public function testCompileWithME()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array(10, 'me')
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'tickets_participants_0.person_id IN (:person_ids_0)',
            array(
                'person_ids_0' => array(10, new TermEngineExpression('agent.getId()')),
            ),
            null,
            'LEFT JOIN tickets_participants tickets_participants_0 ON (tickets_participants_0.ticket_id = ticket.id)'
        );
    }
}
