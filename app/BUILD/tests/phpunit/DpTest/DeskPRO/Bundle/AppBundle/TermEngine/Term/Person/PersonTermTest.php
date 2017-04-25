<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
