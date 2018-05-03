<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContactDataCollectionType.
 */
class ContactDataCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['owner'])
            ->setDefaults([
                'data_class'     => ArrayCollection::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'entry_options'  => function (Options $options) {
                    return [
                        'owner' => $options['owner'],
                    ];
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
