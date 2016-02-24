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

use Application\DeskPRO\Entity\Labels\Label;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class LabelsCollectionTransformer.
 */
class LabelsCollectionTransformer implements DataTransformerInterface
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
     * @var object
     */
    private $labelsOwner;

    /**
     * @var string
     */
    private $labelsClass;

    /**
     * @var string
     */
    private $labelsProperty;

    /**
     * @var string
     */
    private $ownerProperty;

    /**
     * LabelsCollectionTransformer constructor.
     *
     * @param EntityManager    $em
     * @param PropertyAccessor $property_accessor
     * @param object           $labelsOwner
     * @param string           $labelsClass
     * @param string           $labelsProperty
     * @param string           $ownerProperty
     */
    public function __construct(EntityManager $em, PropertyAccessor $property_accessor, $labelsOwner, $labelsClass, $labelsProperty, $ownerProperty)
    {
        $this->em                = $em;
        $this->property_accessor = $property_accessor;
        $this->labelsOwner       = $labelsOwner;
        $this->labelsClass       = $labelsClass;
        $this->labelsProperty    = $labelsProperty;
        $this->ownerProperty     = $ownerProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($labels)
    {
        if (!is_array($labels) && !$labels instanceof \Traversable) {
            return [];
        }

        $result = [];
        foreach ($labels as $label) {
            if (is_string($label)) {
                $result[] = $label;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($labels)
    {
        if (!$this->labelsOwner) {
            throw new \InvalidArgumentException('Labels owner is not defined.');
        }

        $repository = $this->em->getRepository($this->labelsClass);
        $result     = [];

        foreach ($labels as $label) {
            $entity = $repository->findOneBy([
                $this->ownerProperty => $this->labelsOwner,
                'label'              => $label,
            ]);

            if (!$entity) {
                $entity = new $this->labelsClass();
                if (!$entity instanceof Label) {
                    throw new \InvalidArgumentException('Entity '.get_class($entity).' is not instance of '.Label::class);
                }

                $entity->setLabel($label);
                $this->property_accessor->setValue($entity, $this->ownerProperty, $this->labelsOwner);
            }

            $result[] = $entity;
        }

        return $result;
    }
}
