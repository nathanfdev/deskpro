<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketId;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketIdTerm.
 */
class TicketIdTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'num'  => null,
            'num2' => null,
        ]);

        $resolver->setConstraints([
            'num' => [ // an array of numerics
                new Assert\NotNull(),
                new Assert\Type('array'),
                new Assert\All([
                    'constraints' => new Assert\Type('numeric'),
                ]),
            ],
            'num2' => [ // a numeric or null
                new Assert\Type('numeric'),
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
            TermInterface::OP_GT,
            TermInterface::OP_GTE,
            TermInterface::OP_LT,
            TermInterface::OP_LTE,
            TermInterface::OP_NOT_RANGE,
            TermInterface::OP_RANGE,
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
