<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketId\TicketIdTerm
 */
class TicketIdTermSpec extends ObjectBehavior
{
    public function it_is_a_term()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermInterface');
    }

    public function it_defaults_to_is_op()
    {
        $this->getOp()->shouldReturn(TermInterface::OP_IS);
    }

    public function it_lets_you_change_the_op()
    {
        $this->setOp(TermInterface::OP_NOT);

        $this->getOp()->shouldReturn(TermInterface::OP_NOT);
    }

    public function it_defines_its_supported_options()
    {
        $this->getSupportedOps()->shouldBe(
            [
                TermInterface::OP_IS,
                TermInterface::OP_NOT,
                TermInterface::OP_GT,
                TermInterface::OP_GTE,
                TermInterface::OP_LT,
                TermInterface::OP_LTE,
                TermInterface::OP_NOT_RANGE,
                TermInterface::OP_RANGE,
            ]
        );
    }

    public function it_defines_its_options()
    {
        $resolver = $this->getOptionsResolver();
        $resolver->isDefined('num')->shouldBe(true);
        $resolver->isDefined('num2')->shouldBe(true);
        $resolver->getConstraints()->shouldBeLike(
            [
                'num' => [ // an array of numerics
                    new Assert\NotNull(),
                    new Assert\Type('array'),
                    new Assert\All(
                        [
                            'constraints' => new Assert\Type('numeric'),
                        ]
                    ),
                ],
                'num2' => [ // a numeric or null
                    new Assert\Type('numeric'),
                ],
            ]
        );
    }
}
