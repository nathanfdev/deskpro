<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm
 */
class TicketStatusTermSpec extends ObjectBehavior
{
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
        $resolver->isDefined('status')->shouldBe(true);
        $resolver->getConstraints()->shouldBeLike(
            [
                'status' => [
                    new Assert\NotBlank(),
                    new Assert\Type('array'),
                    new Assert\Choice(
                        [
                            'multiple' => true,
                            'choices'  => [
                                Ticket::STATUS_ARCHIVED,
                                Ticket::STATUS_AWAITING_AGENT,
                                Ticket::STATUS_AWAITING_USER,
                                Ticket::STATUS_RESOLVED,
                                Ticket::HIDDEN_STATUS_DELETED,
                                Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_DELETED,
                                Ticket::HIDDEN_STATUS_SPAM,
                                Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_SPAM,
                            ],
                        ]
                    ),
                ],
            ]
        );
    }
}
