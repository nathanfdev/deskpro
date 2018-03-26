<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomData\TicketCustomDataTerm
 */
class TicketCustomDataTermSpec extends ObjectBehavior
{
    public function let()
    {
        $this->setOption('field_id', 1);
    }

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
        $resolver->isDefined('input')->shouldBe(true);
        $resolver->isDefined('values')->shouldBe(true);
        $resolver->isDefined('field_id')->shouldBe(true);
        $resolver->getConstraints()->shouldBeLike(
            [
                'field_id' => [
                    new Assert\NotBlank(),
                    new PrimaryKeyExists(
                        [
                            'table' => 'custom_def_ticket',
                        ]
                    ),
                ],
            ]
        );
    }
}
