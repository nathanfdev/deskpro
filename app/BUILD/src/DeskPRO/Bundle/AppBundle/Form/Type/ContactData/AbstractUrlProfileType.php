<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractUrlProfileType.
 */
abstract class AbstractUrlProfileType extends AbstractContactDataItemType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', TextType::class, [
            'property_path' => 'field_1',
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onParseProfilePath']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'error_mapping' => [
                'field_2' => 'url',
            ],
        ]);
    }

    /**
     * @param FormEvent $event
     */
    abstract public function onParseProfilePath(FormEvent $event);
}
