<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\PersonEmailTerm
 */
class PersonEmailTermSpec extends ObjectBehavior
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
        $resolver->isDefined('email')->shouldBe(true);
        $resolver->getConstraints()->shouldBeLike(
            [
                'email' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ]
        );
    }
}
