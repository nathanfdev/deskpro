<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Labels;

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayOfStringsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LabelsCollectionType.
 */
class LabelsCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(new LabelsCollectionTransformer(
                $options['labels_owner'],
                $options['labels_class'],
                $options['labels_property'],
                $options['owner_property']
            ))
            ->addViewTransformer(new ArrayOfStringsTransformer()
        );
    }

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
            ->setRequired(['labels_class', 'labels_owner', 'labels_property', 'owner_property'])
            ->setDefaults([
                'labels_property' => 'labels',
                'allow_add'       => true,
                'allow_delete'    => true,
                'by_reference'    => true,
                'error_bubbling'  => false,
            ])
            ->setAllowedTypes('labels_class', 'string')
            ->setAllowedTypes('labels_owner', 'object')
            ->setAllowedTypes('labels_property', 'string')
            ->setAllowedTypes('owner_property', 'string')
        ;
    }
}
