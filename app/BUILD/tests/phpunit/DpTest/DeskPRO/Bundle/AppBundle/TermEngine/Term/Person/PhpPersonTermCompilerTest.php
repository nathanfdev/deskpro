<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PersonTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PhpPersonTermCompiler;
use DpTest\DeskProTestCase;

class PhpPersonTermCompilerTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(PhpPersonTermCompiler::class, new PhpPersonTermCompiler());
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractPhpTermCompiler()
    {
        $this->assertContains(AbstractPhpTermCompiler::class, class_parents(PhpPersonTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_PersonTerm_into_a_PhpCheck()
    {
        $term     = new PersonTerm(['person_ids' => [1]]);
        $compiler = new PhpPersonTermCompiler();
        $this->assertInstanceOf(PhpCheck::class, $compiler->compile($term));
    }
}
