<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerProactiveOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessengerProactiveOptionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('greetingTitle', TextType::class)
            ->add('title', TextType::class)
            ->add('buttonText', TextType::class)
            ->add('inputPlaceholder', TextType::class)
            ->add('description', TextType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessengerProactiveOptions::class,
        ]);
    }
}
