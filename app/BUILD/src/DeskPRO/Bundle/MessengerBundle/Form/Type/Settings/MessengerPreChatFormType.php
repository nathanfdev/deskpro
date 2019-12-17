<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\PreChatForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MessengerPreChatFormType.
 */
class MessengerPreChatFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', ApiBooleanType::class)
            ->add('isNameEnabled', ApiBooleanType::class)
            ->add('isEmailEnabled', ApiBooleanType::class)
            ->add('isNameRequired', ApiBooleanType::class)
            ->add('isEmailRequired', ApiBooleanType::class)
            ->add('isDepartmentSelectable', ApiBooleanType::class)
            ->add('fields', CollectionType::class, [
                'property_path' => 'fields',
                'type'          => MessengerPreChatFormCustomFieldType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => PreChatForm::class,
            ])
        ;
    }
}
