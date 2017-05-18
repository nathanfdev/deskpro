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
