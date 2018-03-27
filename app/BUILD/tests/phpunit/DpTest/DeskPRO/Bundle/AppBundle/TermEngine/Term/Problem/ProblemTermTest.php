<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem\ProblemTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskProTestCase;
use Prophecy\Argument;

/**
 * Class ProblemTermTest.
 */
class ProblemTermTest extends DeskProTestCase
{
    public function test_instantiable()
    {
        $term = new ProblemTerm([], TermInterface::OP_IS);
        $this->assertInstanceOf(ProblemTerm::class, $term);
    }

    public function test_extend_AbstractTerm()
    {
        $this->assertContains(AbstractTerm::class, class_parents(ProblemTerm::class));
    }

    public function test_add_constraints_and_default_value_for_the_problem_param()
    {
        $resolver = $this->prophesize(TermOptionsResolver::class);
        $resolver->setDefaults(['problem' => null])->shouldBeCalled();
        $resolver->setConstraints(Argument::type('array'))->shouldBeCalled();
        $resolver->setNormalizer('problem', Argument::type('closure'))->shouldBeCalled();
        $resolver = $resolver->reveal();
        ProblemTerm::configureOptions($resolver);
    }

    public function test_params_normalizer()
    {
        $resolver = new TermOptionsResolver();
        ProblemTerm::configureOptions($resolver);

        $this->assertEquals(['problem' => []], $resolver->resolve([]));
        $this->assertEquals(['problem' => [1, 2]], $resolver->resolve(['problem' => ['1', '2']]));
        $this->assertEquals(['problem' => [1]], $resolver->resolve(['problem' => '1']));
    }
}
