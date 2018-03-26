<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings;

use DeskPRO\Bundle\AppBundle\Model\PusherModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PusherType.
 */
class PusherType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('key', TextType::class)
            ->add('secret', TextType::class)
            ->add('id', TextType::class)
            ->add('cluster', ChoiceType::class, [
                'choices_as_values' => true,
                'required'          => true,
                'choices'           => [
                    PusherModel::PUSHER_CLASTER_US_WEST_1,
                    PusherModel::PUSHER_CLASTER_EU_WEST_1,
                    PusherModel::PUSHER_CLASTER_AP_SOUTHEAST_1,
                    PusherModel::PUSHER_CLASTER_AP_SOUTH_1,
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
            'data_class' => PusherModel::class,
        ]);
    }
}
