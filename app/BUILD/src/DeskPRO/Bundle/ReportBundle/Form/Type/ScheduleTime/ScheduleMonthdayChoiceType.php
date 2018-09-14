<?php

namespace DeskPRO\Bundle\ReportBundle\Form\Type\ScheduleTime;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ScheduleMonthdayChoiceType.
 */
class ScheduleMonthdayChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices_as_values' => true,
            'choices'           => array_merge(range(1, 31), ['last']),
        ]);
    }
}
