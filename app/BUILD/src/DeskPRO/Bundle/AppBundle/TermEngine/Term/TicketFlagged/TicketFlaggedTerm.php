<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketFlaggedTerm.
 */
class TicketFlaggedTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setConstraints([
            'flag' => [
                new Assert\NotBlank(),
            ],
        ]);
        $resolver->setNormalizer(
            'flag',
            function ($options, $value) {
                if (!is_array($value)) {
                    $value = [$value];
                }

                $value = array_map('strtolower', $value);
                $colors = [];
                foreach ($value as $color) {
                    if (is_numeric($color)) {
                        if (isset(TicketFlagged::$colorMap[$color])) {
                            $colors[] = TicketFlagged::$colorMap[$color];
                        }
                    } else {
                        $colors[] = $color;
                    }
                }

                return $colors;
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
