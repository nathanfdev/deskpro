<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData\CustomDataTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskProTestCase;
use Prophecy\Argument;

class CustomDataTermTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $term = new CustomDataTerm([], TermInterface::OP_IS);
        $this->assertInstanceOf(CustomDataTerm::class, $term);
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractTerm()
    {
        $this->assertContains(AbstractTerm::class, class_parents(CustomDataTerm::class));
    }

    /**
     * @test
     */
    public function it_should_add_constraints_and_default_value_for_field_id_and_custom_data_value_params()
    {
        $resolver = $this->prophesize(TermOptionsResolver::class);
        $resolver->setDefaults(['field_id' => null, 'custom_data_value' => null])->shouldBeCalled();
        $resolver->setConstraints(Argument::type('array'))->shouldBeCalled();
        $resolver = $resolver->reveal();
        CustomDataTerm::configureOptions($resolver);
    }
}
