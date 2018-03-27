<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization\OrganizationTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization\PhpOrganizationTermCompiler;
use DpTest\DeskProTestCase;

class PhpOrganizationTermCompilerTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(PhpOrganizationTermCompiler::class, new PhpOrganizationTermCompiler());
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractPhpTermCompiler()
    {
        $this->assertContains(AbstractPhpTermCompiler::class, class_parents(PhpOrganizationTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_OrganizationTerm_into_a_PhpCheck()
    {
        $term     = new OrganizationTerm(['organization' => 1]);
        $compiler = new PhpOrganizationTermCompiler();
        $this->assertInstanceOf(PhpCheck::class, $compiler->compile($term));
    }
}
