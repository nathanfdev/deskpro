<?php

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
