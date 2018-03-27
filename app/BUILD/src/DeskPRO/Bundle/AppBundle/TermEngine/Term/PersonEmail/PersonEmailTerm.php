<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonEmailTerm.
 */
class PersonEmailTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'email' => '',
        ]);
        $resolver->setConstraints([
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(['strict' => true]),
            ],
        ]);
        $resolver->setNormalizer(
            'email',
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
