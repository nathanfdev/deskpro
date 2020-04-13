<?php

namespace DeskPRO\Bundle\ApiBundle\Form\Type\Batch;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BatchRequestsType.
 */
class BatchRequestsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'entry_type'   => BatchRequestType::class,
                'allow_add'    => true,
                'allow_delete' => true,
            ])
        ;
    }
}
