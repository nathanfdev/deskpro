<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as CoreDateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonOnboardingType.
 */
class PersonOnboardingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', IntegerType::class, [
                'required' => true,
            ])
            ->add('date_completion', CoreDateTimeType::class, [
                'widget'   => 'single_text',
                'required' => false,
            ])
            ->add('current_step', IntegerType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PersonOnboarding::class,
        ]);
    }
}
