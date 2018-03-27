<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem\DbalProblemTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem\ProblemTerm;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalProblemTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(DbalProblemTermCompiler::class, new DbalProblemTermCompiler());
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractDbalTermCompiler()
    {
        $this->assertContains(AbstractDbalTermCompiler::class, class_parents(DbalProblemTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_ProblemTerm_into_a_DbalQueryPart()
    {
        /** @var DbalProblemTermCompiler $compiler */
        $compiler = $this->get('term_engine.dbal_ticket_filters.compiler.problem');
        $term     = new ProblemTerm(['problem' => 1]);
        $this->assertInstanceOf(DbalQueryPart::class, $compiler->doCompile($term));
    }
}
