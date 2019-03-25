<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\MoneyType as BaseMoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Override base class to be able to set rounding strategy.
 *
 * Class DpMoneyType.
 */
class MoneyType extends BaseMoneyType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['currency_code'] = strtolower($options['currency']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(new MoneyToLocalizedStringTransformer(
                $options['scale'],
                $options['grouping'],
                $options['rounding_mode'],
                $options['divisor']
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'rounding_mode' => MoneyToLocalizedStringTransformer::ROUND_DOWN,
        ]);
    }
}
