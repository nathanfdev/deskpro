<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization\OrganizationTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskProTestCase;
use Prophecy\Argument;

class OrganizationTermTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $term = new OrganizationTerm([], TermInterface::OP_IS);
        $this->assertInstanceOf(OrganizationTerm::class, $term);
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractTerm()
    {
        $this->assertContains(AbstractTerm::class, class_parents(OrganizationTerm::class));
    }

    /**
     * @test
     */
    public function it_should_add_constraints_and_default_value_for_the_organization_param()
    {
        $resolver = $this->prophesize(TermOptionsResolver::class);
        $resolver->setDefaults(['organization' => null])->shouldBeCalled();
        $resolver->setConstraints(Argument::type('array'))->shouldBeCalled();
        $resolver = $resolver->reveal();
        OrganizationTerm::configureOptions($resolver);
    }
}
