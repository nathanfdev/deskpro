<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization\DbalOrganizationTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization\OrganizationTerm;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalOrganizationTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(DbalOrganizationTermCompiler::class, new DbalOrganizationTermCompiler());
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractDbalTermCompiler()
    {
        $this->assertContains(AbstractDbalTermCompiler::class, class_parents(DbalOrganizationTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_OrganizationTerm_into_a_DbalQueryPart()
    {
        /** @var DbalOrganizationTermCompiler $compiler */
        $compiler = $this->get('term_engine.dbal_ticket_filters.compiler.organization');
        $term     = new OrganizationTerm(['organization' => 1]);
        $this->assertInstanceOf(DbalQueryPart::class, $compiler->doCompile($term));
    }
}
