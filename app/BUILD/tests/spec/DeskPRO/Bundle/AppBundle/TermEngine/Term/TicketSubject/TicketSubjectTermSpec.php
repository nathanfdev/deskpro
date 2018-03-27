<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject\TicketSubjectTerm
 */
class TicketSubjectTermSpec extends ObjectBehavior
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
        $this->getSupportedOps()->shouldBeLike(
            [
                TermInterface::OP_IS,
                TermInterface::OP_NOT,
                TermInterface::OP_HAS,
                TermInterface::OP_NOT_HAS,
            ]
        );
    }

    public function it_defines_its_options()
    {
        $resolver = $this->getOptionsResolver();
        $resolver->isDefined('subject')->shouldBe(true);
        $resolver->isDefined('wildcard_prefix')->shouldBe(true);
        $resolver->isDefined('wildcard_postfix')->shouldBe(true);
        $resolver->getConstraints()->shouldBeLike(
            [
                'subject' => [
                    new Assert\NotNull(),
                    new Assert\All(
                        [
                            'constraints' => new Assert\NotBlank(),
                        ]
                    ),
                ],
            ]
        );
    }
}
