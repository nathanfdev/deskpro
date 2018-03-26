<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm
 */
class AgentTermSpec extends ObjectBehavior
{
    public function it_is_a_term()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermInterface');
    }

    public function it_defines_its_options()
    {
        $resolver = $this->getOptionsResolver();
        $resolver->isDefined('agent_ids')->shouldBe(true);
        $resolver->getConstraints()->shouldBeLike(
            [
                'agent_ids' => [
                    new NotBlank(),
                    new Type('array'),
                    new PrimaryKeyExists(
                        [
                            'table'           => 'people',
                            'excluded_values' => AgentTerm::ID_ME,
                        ]
                    ),
                ],
            ]
        );
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
}
