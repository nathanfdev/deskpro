<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SingleCheckboxType.
 */
class SingleCheckboxType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'single_checkbox';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CheckboxType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['toggle_checkbox'] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'force_boolean'  => false,
            'checkbox_label' => '',
        ]);
    }
}
