<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AgentTerm.
 */
class AgentTerm extends AbstractTerm
{
    /**
     * A special string ID that represents the currently logged in agent.
     */
    const ID_ME = 'me';

    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'agent_ids' => [],
        ]);
        $resolver->setConstraints([
            'agent_ids' => [
                new Assert\NotBlank(),
                new Assert\Type('array'),
                new PrimaryKeyExists([
                    'table'           => 'people',
                    'excluded_values' => self::ID_ME,
                ]),
            ],
        ]);
        $resolver->setNormalizer(
            'agent_ids',
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
