<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketParticipantTerm.
 */
class TicketParticipantTerm extends AbstractTerm
{
    /**
     * A special string id to represent the currently logged in user.
     */
    const ID_ME = 'me';

    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'person_ids' => [],
        ]);

        $resolver->setConstraints([
            'person_ids' => [
                new Assert\NotBlank(),
                new Assert\Type('array'),
                new PrimaryKeyExists([
                    'table'           => 'people',
                    'excluded_values' => [self::ID_ME],
                ]),
            ],
        ]);
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
