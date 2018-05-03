<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractContactDataItemType.
 */
abstract class AbstractContactDataItemType extends AbstractType implements ContactDataTypeGroupInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'contact_type' => static::getContactType(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ContactDataItemType::class;
    }
}
