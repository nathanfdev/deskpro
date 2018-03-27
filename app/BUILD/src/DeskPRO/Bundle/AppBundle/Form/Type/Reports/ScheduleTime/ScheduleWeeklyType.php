<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports\ScheduleTime;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ScheduleWeeklyType.
 */
class ScheduleWeeklyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('weekday', ChoiceType::class, [
            'required'          => true,
            'choices_as_values' => true,
            'choices'           => [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
            ],
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ScheduleWhenType::class;
    }
}
