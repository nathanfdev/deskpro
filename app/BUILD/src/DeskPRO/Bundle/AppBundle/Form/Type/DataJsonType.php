<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DataJsonType extends TextType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmitSerialize']);
    }

    /**
     * @param FormEvent $event
     *
     * @return string
     */
    public function onPreSubmitSerialize(FormEvent $event)
    {
        $serializedData = null;
        $data           = $event->getData();

        $decoded = @json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $encodedData = json_encode($decoded, JSON_NUMERIC_CHECK);
            if (json_last_error() === JSON_ERROR_NONE && is_string($encodedData)) {
                $serializedData = $encodedData;
            }
        } else {
            $serializedData = $data;
        }

        if (is_string($serializedData)) {
            $event->setData($serializedData);
        }
    }
}
