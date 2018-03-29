<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketUrgency;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketUrgencyTerm.
 */
class TicketUrgencyTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'num' => null,
        ]);
        $resolver->setConstraints([
            'num' => [ // an array of numerics
                new Assert\Type('array'),
                new Assert\All([
                    'constraints' => [
                        new Assert\Type('numeric'),
                        new Assert\Range([
                            'min' => 1,
                            'max' => 10,
                        ]),
                    ],
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
