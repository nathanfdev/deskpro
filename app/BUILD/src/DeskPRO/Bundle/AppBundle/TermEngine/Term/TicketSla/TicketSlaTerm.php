<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSla;

use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketSlaTerm.
 */
class TicketSlaTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setConstraints([
            'sla' => [
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\NotBlank(),
                    new Assert\Type('integer'),
                ]),
            ],
            'status' => [
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\NotBlank(),
                    new Assert\Choice([
                        TicketSla::STATUS_OK,
                        TicketSla::STATUS_WARNING,
                        TicketSla::STATUS_FAIL,
                    ]),
                ]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedOps()
    {
        return [TermInterface::OP_HAS, TermInterface::OP_NOT_HAS];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOp()
    {
        return TermInterface::OP_HAS;
    }
}
