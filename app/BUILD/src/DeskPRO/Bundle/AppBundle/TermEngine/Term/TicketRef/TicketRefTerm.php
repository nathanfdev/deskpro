<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketRefTerm.
 */
class TicketRefTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'ref' => null,
        ]);
        $resolver->setConstraints([
            'ref' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
            ],
        ]);

        $resolver->setNormalizer(
            'ref',
            function ($options, $value_array) {
                if (!is_array($value_array)) {
                    $value_array = [$value_array];
                }

                return array_map('strtoupper', $value_array);
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
