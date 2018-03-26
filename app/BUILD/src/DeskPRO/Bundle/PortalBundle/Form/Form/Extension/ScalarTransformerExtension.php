<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Ensure that uncompounded forms accept scalar values to prevent array to string conversion errors.
 *
 * Class ScalarTransformerExtension.
 */
class ScalarTransformerExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // multi choice is not compound but accepts an array of values
        $isMultiChoice = isset($options['expanded'])
            && isset($options['multiple'])
            && !$options['expanded']
            && $options['multiple'];

        if (!$options['compound'] && !$isMultiChoice) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 1000);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!is_scalar($data)) {
            $event->setData(null);
        }
    }
}
