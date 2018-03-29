<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Person;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonTerm.
 */
class PersonTerm extends AbstractTerm
{
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
                    'table' => 'people',
                ]),
            ],
        ]);
        $resolver->setNormalizer(
            'person_ids',
            function ($options, $value) {
                if (!is_array($value)) {
                    $value = [$value];
                }

                $value = array_map(
                    function ($id) {
                        return (int) $id;
                    },
                    $value
                );

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
