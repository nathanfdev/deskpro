<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Person;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PersonTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskProTestCase;
use Prophecy\Argument;

/**
 * Class PersonTermTest.
 */
class PersonTermTest extends DeskProTestCase
{
    public function test_instantiable()
    {
        $term = new PersonTerm([], TermInterface::OP_IS);
        $this->assertInstanceOf(PersonTerm::class, $term);
    }

    public function test_extend_AbstractTerm()
    {
        $this->assertContains(AbstractTerm::class, class_parents(PersonTerm::class));
    }

    public function test_add_constraints_and_default_value_for_the_person_ids_param()
    {
        $resolver = $this->prophesize(TermOptionsResolver::class);
        $resolver->setNormalizer('person_ids', Argument::type('closure'))->shouldBeCalled();
        $resolver->setDefaults(['person_ids' => []])->shouldBeCalled();
        $resolver->setConstraints(Argument::type('array'))->shouldBeCalled();
        $resolver = $resolver->reveal();
        PersonTerm::configureOptions($resolver);
    }

    public function test_params_normalizer()
    {
        $resolver = new TermOptionsResolver();
        PersonTerm::configureOptions($resolver);

        $this->assertEquals(['person_ids' => []], $resolver->resolve([]));
        $this->assertEquals(['person_ids' => [1, 2]], $resolver->resolve(['person_ids' => ['1', '2']]));
        $this->assertEquals(['person_ids' => [1]], $resolver->resolve(['person_ids' => '1']));
    }
}
