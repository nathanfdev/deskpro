<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AbstractAppSettingsType.
 */
class AppSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('tab_enabled', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('subscriptions', ApiBooleanType::class, [
                'required' => false,
            ])
        ;
    }
}
