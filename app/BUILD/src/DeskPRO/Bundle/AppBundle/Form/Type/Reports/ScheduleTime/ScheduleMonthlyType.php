<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports\ScheduleTime;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ScheduleMonthlyType.
 */
class ScheduleMonthlyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('monthday', ScheduleMonthdayChoiceType::class, [
            'required'    => true,
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
