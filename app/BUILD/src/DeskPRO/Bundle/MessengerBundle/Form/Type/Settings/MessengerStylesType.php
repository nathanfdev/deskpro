<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerStyles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessengerStylesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('backgroundColor', TextType::class)
            ->add('primaryColor', TextType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessengerStyles::class,
        ]);
    }
}
