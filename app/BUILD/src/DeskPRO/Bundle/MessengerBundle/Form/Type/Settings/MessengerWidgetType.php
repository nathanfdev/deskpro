<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerWidget;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MessengerWidgetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('primaryColor', TextType::class)
            ->add('backgroundColor', TextType::class)
            ->add('textColor', TextType::class)
            ->add('greetingTitle', TextType::class)
            ->add('position', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    MessengerWidget::POSITION_LEFT,
                    MessengerWidget::POSITION_RIGHT,
                ],
                'choices_as_values' => true,
                'constraints'       => [
                    new Assert\NotNull(),
                ],

            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessengerWidget::class,
        ]);
    }
}
