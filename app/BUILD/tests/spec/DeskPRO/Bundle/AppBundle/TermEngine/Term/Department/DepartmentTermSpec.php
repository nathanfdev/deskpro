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

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm
 */
class DepartmentTermSpec extends ObjectBehavior
{
    public function it_is_a_term()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermInterface');
    }

    public function it_has_default_op_is()
    {
        $this->getOp()->shouldBe(TermInterface::OP_IS);
    }

    public function it_allows_op_change()
    {
        $this->setOp(TermInterface::OP_NOT);

        $this->getOp()->shouldBe(TermInterface::OP_NOT);
    }

    public function it_defines_its_options()
    {
        $resolver = $this->getOptionsResolver();
        $resolver->isDefined('department_ids')->shouldBe(true);
        $resolver->getConstraints()->shouldBeLike(
            [
                'department_ids' => [
                    new Assert\NotBlank(),
                    new Assert\Type('array'),
                    new PrimaryKeyExists(
                        [
                            'table' => 'departments',
                        ]
                    ),
                ],
            ]
        );
    }
}
