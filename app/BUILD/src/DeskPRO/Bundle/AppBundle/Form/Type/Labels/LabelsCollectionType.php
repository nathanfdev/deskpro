<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\Labels;

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayOfStringsTransformer;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class LabelsCollectionType.
 */
class LabelsCollectionType extends ApiType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PropertyAccessor
     */
    private $property_accessor;

    /**
     * Constructor.
     *
     * @param EntityManager    $em
     * @param PropertyAccessor $property_accessor
     */
    public function __construct(EntityManager $em, PropertyAccessor $property_accessor)
    {
        $this->em                = $em;
        $this->property_accessor = $property_accessor;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(new LabelsCollectionTransformer(
                $this->em,
                $this->property_accessor,
                $options['labels_owner'],
                $options['labels_class'],
                $options['labels_property'],
                $options['owner_property']
            ))
            ->addViewTransformer(new ArrayOfStringsTransformer(true)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'api_labels_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(['labels_class', 'labels_owner', 'labels_property', 'owner_property'])
            ->setDefaults([
                'labels_property' => 'labels',
                'allow_add'       => true,
                'allow_delete'    => true,
                'by_reference'    => true,
            ])
        ;
    }
}
