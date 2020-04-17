<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerProactive;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MessengerProactiveType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('autoStart', ApiBooleanType::class)
            ->add('autoStartTimeout', NumberType::class)
            ->add('autoStartStyle', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    MessengerProactive::STYLE_AVATAR_TEXT_BUTTON,
                    MessengerProactive::STYLE_AVATAR_TEXT_INPUT,
                    MessengerProactive::STYLE_AVATAR_BUTTON,
                    MessengerProactive::STYLE_TEXT_BUTTON,
                    MessengerProactive::STYLE_TEXT_INPUT,
                    MessengerProactive::STYLE_AVATAR_WIDGET,
                ],
                'choices_as_values' => true,
                'constraints'       => [
                    new Assert\NotNull(),
                ],

            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessengerProactive::class,
        ]);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ((int) $data['autoStartTimeout'] < 0) {
            $data['autoStartTimeout'] = 0;
        }
        $event->setData($data);
    }
}
