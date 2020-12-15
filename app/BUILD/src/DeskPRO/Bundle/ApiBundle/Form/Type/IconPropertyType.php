<?php

namespace DeskPRO\Bundle\ApiBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Entity\IconProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class IconPropertyType.
 */
class IconPropertyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('urn', TextType::class, [
                'required' => true
            ])
            ->add('style', TextType::class, [
                'mapped' => false,
            ])
            ->add('color', TextType::class, [
                'mapped' => false,
            ])
            ->add('options', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'allow_add'  => true
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        $options = [];
        if (isset($data['style'])) {
            $options['style'] = $data['style'];
        }

        if (isset($data['color'])) {
            $options['color'] = $data['color'];
        }

        $data['options'] = $options;
        $event->setData($data);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => IconProperty::class,
        ]);
    }

}
