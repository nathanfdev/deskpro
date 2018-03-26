<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @return string
     */
    public function onPreSubmitSerialize(FormEvent $event)
    {
        $serializedData = null;
        $data = $event->getData();

        $encodedData = json_encode($data, JSON_NUMERIC_CHECK);
        if (json_last_error() === JSON_ERROR_NONE && is_string($encodedData)) {
            $serializedData = $encodedData;
        }

        if (is_string($serializedData)) {
            $event->setData($serializedData);
        }
    }

}

