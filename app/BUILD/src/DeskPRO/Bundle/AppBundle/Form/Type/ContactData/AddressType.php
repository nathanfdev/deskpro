<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AddressType.
 */
class AddressType extends AbstractContactDataItemType
{
    /**
     * {@inheritdoc}
     */
    public static function getContactType()
    {
        return ContactDataAbstract::TYPE_ADDRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', TextareaType::class, [
                'property_path' => 'field_1',
            ])
            ->add('city', TextType::class, [
                'property_path' => 'field_2',
            ])
            ->add('state', TextType::class, [
                'property_path' => 'field_3',
            ])
            ->add('zip', TextType::class, [
                'property_path' => 'field_4',
            ])
            ->add('country', TextType::class, [
                'property_path' => 'field_5',
            ])
        ;
    }
}
