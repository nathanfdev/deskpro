<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage\DbalTicketLanguageTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage\TicketLanguageTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

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
                'language' => '1',
            )
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{languages}.id = :language OR {languages}.lang_code = :language');
        $this->assertParameters(
            $query_part,
            array(
                'language' => '1',
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'languages' => array(
                    'table' => 'languages',
                    'on'    => '{languages}.id = ticket.language_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testCompileIsNOT()
    {
        $term = new TicketLanguageTerm(
            array(
                'language' => '1',
            ),
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            '({languages}.id != :language AND {languages}.lang_code != :language) OR {languages}.id IS NULL'
        );
        $this->assertParameters(
            $query_part,
            array(
                'language' => '1',
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'languages' => array(
                    'table' => 'languages',
                    'on'    => '{languages}.id = ticket.language_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testLangCodeCompileIs()
    {
        $term = new TicketLanguageTerm(
            array(
                'language' => 'eng',
            )
        );
        $query_part = $this->term_compiler->compile($term);
        $this->assertWhere($query_part, '{languages}.id = :language OR {languages}.lang_code = :language');
        $this->assertParameters(
            $query_part,
            array(
                'language' => 'eng',
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'languages' => array(
                    'table' => 'languages',
                    'on'    => '{languages}.id = ticket.language_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testLangCodeCompileIsNOT()
    {
        $term = new TicketLanguageTerm(
            array(
                'language' => 'ger',
            ),
            TermInterface::OP_NOT
        );
        $query_part = $this->term_compiler->compile($term);
        $this->assertWhere(
            $query_part,
            '({languages}.id != :language AND {languages}.lang_code != :language) OR {languages}.id IS NULL'
        );
        $this->assertParameters(
            $query_part,
            array(
                'language' => 'ger',
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'languages' => array(
                    'table' => 'languages',
                    'on'    => '{languages}.id = ticket.language_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }
}
