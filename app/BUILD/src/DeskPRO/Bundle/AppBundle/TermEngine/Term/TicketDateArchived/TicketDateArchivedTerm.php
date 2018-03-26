<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateArchived;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketDateArchivedTerm.
 */
class TicketDateArchivedTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'date'        => null,
            'date2'       => null,
            'ignore_time' => false,
        ]);

        $date_normalizer = function ($options, $date) {
            if (!$date) {
                return;
            }

            if ($date instanceof \DateTime || $date instanceof \DateTimeZone) {
                return $date;
            }

            return new \DateTime((string) $date);
        };

        $resolver->setNormalizers([
            'date'        => $date_normalizer,
            'date2'       => $date_normalizer,
            'ignore_time' => function ($options, $ignore_time) {
                return (bool) $ignore_time;
            },
        ]);

        $resolver->setConstraints([
            'date' => [
                new Assert\NotNull(),
                new Assert\DateTime(),
            ],
            'date2' => [
                new Assert\DateTime(),
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
