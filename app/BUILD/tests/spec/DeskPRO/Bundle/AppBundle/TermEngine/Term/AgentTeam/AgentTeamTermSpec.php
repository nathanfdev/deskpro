<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm
 */
class AgentTeamTermSpec extends ObjectBehavior
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
        $resolver->isDefined('agent_team_ids')->shouldBe(true);
        $resolver->getConstraints()->shouldBeLike(
            [
                'agent_team_ids' => [
                    new Assert\NotBlank(),
                    new Assert\Type('array'),
                    new PrimaryKeyExists(
                        [
                            'table'           => 'agent_teams',
                            'excluded_values' => [AgentTeamTerm::TEAM_ID_ME],
                        ]
                    ),
                ],
            ]
        );
    }
}
