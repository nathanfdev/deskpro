<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Facebook\Type;

use Application\DeskPRO\Facebook\EditApp;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditAppType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('app_id', 'text', ['required' => true]);
        $builder->add('app_secret', 'text', ['required' => true]);
        $builder->add('name', 'text', ['required' => true]);
        $builder->add('icon_url', 'text', ['required' => false]);
        $builder->add('logo_url', 'text', ['required' => false]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => EditApp::class,
                'cascade_validation' => true,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app';
    }
}
