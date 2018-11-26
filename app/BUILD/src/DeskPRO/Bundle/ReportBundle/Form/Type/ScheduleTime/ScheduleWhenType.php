<?php

namespace DeskPRO\Bundle\ReportBundle\Form\Type\ScheduleTime;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ScheduleWhenType.
 */
class ScheduleWhenType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('time', TextType::class, [
            'required'    => true,
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
    }
}
