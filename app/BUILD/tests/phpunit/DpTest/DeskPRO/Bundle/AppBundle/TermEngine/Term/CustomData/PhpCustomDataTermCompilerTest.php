<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData\CustomDataTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData\PhpCustomDataTermCompiler;
use DpTest\DeskProTestCase;

class PhpCustomDataTermCompilerTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(PhpCustomDataTermCompiler::class, new PhpCustomDataTermCompiler());
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractPhpTermCompiler()
    {
        $this->assertContains(AbstractPhpTermCompiler::class, class_parents(PhpCustomDataTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_CustomDataTerm_into_a_PhpCheck()
    {
        $term     = new CustomDataTerm(['field_id' => 1, 'custom_data_value' => 1]);
        $compiler = new PhpCustomDataTermCompiler();
        $this->assertInstanceOf(PhpCheck::class, $compiler->compile($term));
    }
}
