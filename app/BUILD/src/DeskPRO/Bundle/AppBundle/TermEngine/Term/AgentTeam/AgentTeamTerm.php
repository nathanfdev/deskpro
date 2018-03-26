<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AgentTeamTerm.
 */
class AgentTeamTerm extends AbstractTerm
{
    /**
     * A special string id to represent the currently logged in agent's team.
     */
    const TEAM_ID_ME = 'me';

    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'agent_team_ids' => [],
        ]);
        $resolver->setConstraints([
            'agent_team_ids' => [
                new Assert\NotBlank(),
                new Assert\Type('array'),
                new PrimaryKeyExists([
                    'table'           => 'agent_teams',
                    'excluded_values' => [self::TEAM_ID_ME],
                ]),
            ],
        ]);
        $resolver->setNormalizer(
            'agent_team_ids',
            function ($options, $value) {
                if (!is_array($value)) {
                    $value = [$value];
                }

                return $value;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedOps()
    {
        return [TermInterface::OP_IS, TermInterface::OP_NOT];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOp()
    {
        return TermInterface::OP_IS;
    }
}
