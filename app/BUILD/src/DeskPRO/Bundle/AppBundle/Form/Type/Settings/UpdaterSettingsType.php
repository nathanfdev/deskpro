<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\UpdaterSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UpdaterSettingsType.
 */
class UpdaterSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_enabled', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('interval_days', NumberType::class, [
                'required' => false,
            ])
            ->add('time_of_day', TextType::class, [
                'required' => false,
            ])
            ->add('timezone', TimezoneType::class, [
                'required' => false,
            ])
        ;

        $builder->get('timezone')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            if (!empty($data) && $data instanceof \DateTimeZone) {
                $event->setData($data->getName());
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpdaterSettings::class,
        ]);
    }
}
