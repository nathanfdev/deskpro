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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomData;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketCustomDataTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomData\TicketCustomDataTerm;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketCustomDataTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketCustomDataTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_custom_data');
    }

    public function testCompileIsAValue()
    {
        $term = new TicketCustomDataTerm(
            [
                'field_id' => 1,
                'values'   => [3, 11],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{custom_data}.value IN (:values)');
        $this->assertParameters(
            $query_part,
            [
                'values'   => [3, 11],
                'field_id' => 1,
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'custom_data' => [
                    'table' => 'custom_data_ticket',
                    'on'    => '{custom_data}.ticket_id = ticket.id AND {custom_data}.field_id = :field_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileIsAnInput()
    {
        $term = new TicketCustomDataTerm(
            [
                'field_id' => 1,
                'input'    => 'my string input',
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{custom_data}.input = :input');
        $this->assertParameters(
            $query_part,
            [
                'input'    => 'my string input',
                'field_id' => 1,
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'custom_data' => [
                    'table' => 'custom_data_ticket',
                    'on'    => '{custom_data}.ticket_id = ticket.id AND {custom_data}.field_id = :field_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }
}
