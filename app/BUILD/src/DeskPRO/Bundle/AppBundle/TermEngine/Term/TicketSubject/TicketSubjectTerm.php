<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketSubjectTerm.
 */
class TicketSubjectTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'subject'          => null,
            'wildcard_prefix'  => false,
            'wildcard_postfix' => false,
        ]);
        $resolver->setConstraints([
            'subject' => [
                new Assert\NotNull(),
                new Assert\All([
                    'constraints' => new Assert\NotBlank(),
                ]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedOps()
    {
        return [
            TermInterface::OP_IS,
            TermInterface::OP_NOT,
            TermInterface::OP_HAS,
            TermInterface::OP_NOT_HAS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOp()
    {
        return TermInterface::OP_IS;
    }
}
