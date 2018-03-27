<?php

namespace DeskPRO\Bundle\AppBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ForceNullExtension.
 *
 * Make compound forms compliant with application/x-www-form-encoded style.
 *
 * @see \FOS\RestBundle\Decoder\JsonToFormDecoder
 */
class ForceNullExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['compound']) {
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

        if (!$data && $data !== '0') {
            $event->setData(null);
        }
    }
}
