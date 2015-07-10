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

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguageTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketParticipantTermCompiler;

class DbalTicketLanguageTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketLanguageTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_language');
    }

    public function testCompileIs()
    {
        $term = new TicketLanguageTerm(
            array(
                'language' => 'eng'
            )
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{languages}.lang_code = :language');
        $this->assertParameters(
            $query_part,
            array(
                'lang_codes' => 'eng'
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'languages' => array(
                    'table' => 'tickets_languages',
                    'on' => '{languages}.id = ticket.language_id',
                    'type' => DbalQuery::JOIN_LEFT
                )
            )
        );
    }
    /*
    public function testCompileIsNOT()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array(14)
            ),
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{participants}.person_id NOT IN (:person_ids)');
        $this->assertParameters(
            $query_part,
            array(
                'person_ids' => array(14)
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'participants' => array(
                    'table' => 'tickets_participants',
                    'on' => '{participants}.ticket_id = ticket.id',
                    'type' => DbalQuery::JOIN_LEFT
                )
            )
        );
    }

    public function testCompileWithME()
    {
        $term = new TicketParticipantTerm(
            array(
                'person_ids' => array(10, TicketParticipantTerm::ID_ME)
            )
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{participants}.person_id IN (:person_ids)');
        $this->assertParameters(
            $query_part,
            array(
                'person_ids' => array(10, new TermEngineExpression('agent.getId()'))
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'participants' => array(
                    'table' => 'tickets_participants',
                    'on' => '{participants}.ticket_id = ticket.id',
                    'type' => DbalQuery::JOIN_LEFT
                )
            )
        );
    }
    */
}
