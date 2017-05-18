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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage\DbalTicketLanguageTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage\TicketLanguageTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

/**
 * Class DbalTicketLanguageTermCompilerTest.
 */
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
            [
                'language' => '1',
            ]
        );

        $qp = $this->term_compiler->compile($term);
        $this->assertWhere($qp, '{languages}.id IN(:language) OR {languages}.lang_code IN(:language)');
        $this->assertParameters(
            $qp,
            [
                'language' => ['1'],
            ]
        );
        $this->assertUniqueJoins(
            $qp,
            [
                'languages' => [
                    'table' => 'languages',
                    'on'    => '{languages}.id = ticket.language_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileIsNOT()
    {
        $term = new TicketLanguageTerm(
            [
                'language' => '1',
            ],
            TermInterface::OP_NOT
        );

        $qp = $this->term_compiler->compile($term);
        $this->assertWhere(
            $qp,
            '({languages}.id NOT IN(:language) AND {languages}.lang_code NOT IN(:language)) OR {languages}.id IS NULL'
        );
        $this->assertParameters(
            $qp,
            [
                'language' => ['1'],
            ]
        );
        $this->assertUniqueJoins(
            $qp,
            [
                'languages' => [
                    'table' => 'languages',
                    'on'    => '{languages}.id = ticket.language_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testLangCodeCompileIs()
    {
        $term = new TicketLanguageTerm(
            [
                'language' => 'eng',
            ]
        );

        $qp = $this->term_compiler->compile($term);
        $this->assertWhere($qp, '{languages}.id IN(:language) OR {languages}.lang_code IN(:language)');
        $this->assertParameters(
            $qp,
            [
                'language' => ['eng'],
            ]
        );
        $this->assertUniqueJoins(
            $qp,
            [
                'languages' => [
                    'table' => 'languages',
                    'on'    => '{languages}.id = ticket.language_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testLangCodeCompileIsNOT()
    {
        $term = new TicketLanguageTerm(
            [
                'language' => 'ger',
            ],
            TermInterface::OP_NOT
        );

        $qp = $this->term_compiler->compile($term);
        $this->assertWhere(
            $qp,
            '({languages}.id NOT IN(:language) AND {languages}.lang_code NOT IN(:language)) OR {languages}.id IS NULL'
        );
        $this->assertParameters(
            $qp,
            [
                'language' => ['ger'],
            ]
        );
        $this->assertUniqueJoins(
            $qp,
            [
                'languages' => [
                    'table' => 'languages',
                    'on'    => '{languages}.id = ticket.language_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }
}
