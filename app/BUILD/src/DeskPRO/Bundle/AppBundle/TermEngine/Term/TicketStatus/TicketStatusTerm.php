<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketStatusTerm.
 */
class TicketStatusTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults(['status' => null]);
        $resolver->setConstraints([
            'status' => [
                new Assert\NotBlank(),
                new Assert\Type('array'),
                new Assert\Choice([
                    'multiple' => true,
                    'choices'  => [
                        Ticket::STATUS_ARCHIVED,
                        Ticket::STATUS_AWAITING_AGENT,
                        Ticket::STATUS_AWAITING_USER,
                        Ticket::STATUS_RESOLVED,
                        Ticket::HIDDEN_STATUS_DELETED,
                        Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_DELETED,
                        Ticket::HIDDEN_STATUS_SPAM,
                        Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_SPAM,
                    ],
                ]),
            ],
        ]);

        $resolver->setNormalizer(
            'status',
            function ($options, $status_array) {
                if (!is_array($status_array)) {
                    $status_array = [$status_array];
                }

                // if the passed status is a "hidden" sub-status, the compilers
                // are expecting to have "hidden." prepended. We ensure that here.
                $normalized = [];

                foreach ($status_array as $status) {
                    if (in_array($status, [Ticket::HIDDEN_STATUS_SPAM, Ticket::HIDDEN_STATUS_DELETED])) {
                        $status = Ticket::STATUS_HIDDEN.'.'.$status;
                    }

                    $normalized[] = $status;
                }

                return $normalized;
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
