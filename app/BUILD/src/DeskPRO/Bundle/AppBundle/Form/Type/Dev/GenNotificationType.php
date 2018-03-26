<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Dev;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GenNotificationType.
 */
class GenNotificationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('agent', EntityType::class, [
            'class'       => Person::class,
            'required'    => true,
            'constraints' => [
                new Assert\NotNull(),
                new AppAssert\Person\PersonType([
                    'type' => 'agent',
                ]),
            ],
        ]);
    }
}
