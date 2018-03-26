<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData\CustomDataTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData\DbalCustomDataTermCompiler;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalCustomDataTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(DbalCustomDataTermCompiler::class, new DbalCustomDataTermCompiler($this->getContainer()->get('doctrine.orm.default_entity_manager')));
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractDbalTermCompiler()
    {
        $this->assertContains(AbstractDbalTermCompiler::class, class_parents(DbalCustomDataTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_CustomDataTerm_into_a_DbalQueryPart()
    {
        /** @var DbalCustomDataTermCompiler $compiler */
        $compiler = $this->get('term_engine.dbal_ticket_filters.compiler.custom_data');
        $term     = new CustomDataTerm(['field_id' => 1, 'custom_data_value' => 1]);
        $this->assertInstanceOf(DbalQueryPart::class, $compiler->doCompile($term));
    }
}
