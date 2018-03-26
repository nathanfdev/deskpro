<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem\PhpProblemTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem\ProblemTerm;
use DpTest\DeskProTestCase;

class PhpProblemTermCompilerTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(PhpProblemTermCompiler::class, new PhpProblemTermCompiler());
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractPhpTermCompiler()
    {
        $this->assertContains(AbstractPhpTermCompiler::class, class_parents(PhpProblemTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_ProblemTerm_into_a_PhpCheck()
    {
        $term     = new ProblemTerm(['problem' => 1]);
        $compiler = new PhpProblemTermCompiler();
        $this->assertInstanceOf(PhpCheck::class, $compiler->compile($term));
    }
}
