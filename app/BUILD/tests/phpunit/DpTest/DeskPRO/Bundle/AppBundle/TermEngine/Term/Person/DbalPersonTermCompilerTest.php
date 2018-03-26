<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\DbalPersonTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PersonTerm;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalPersonTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(DbalPersonTermCompiler::class, new DbalPersonTermCompiler());
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractDbalTermCompiler()
    {
        $this->assertContains(AbstractDbalTermCompiler::class, class_parents(DbalPersonTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_PersonTerm_into_a_DbalQueryPart()
    {
        /** @var DbalPersonTermCompiler $compiler */
        $compiler = $this->get('term_engine.dbal_ticket_filters.compiler.person');
        $term     = new PersonTerm(['person_ids' => [1]]);
        $this->assertInstanceOf(DbalQueryPart::class, $compiler->doCompile($term));
    }
}
