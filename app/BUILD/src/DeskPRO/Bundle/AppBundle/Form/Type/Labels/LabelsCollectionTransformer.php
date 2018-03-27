<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Labels;

use Application\DeskPRO\Entity\Labels\Label;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class LabelsCollectionTransformer.
 */
class LabelsCollectionTransformer implements DataTransformerInterface
{
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
     * @param object $labelsOwner
     * @param string $labelsClass
     * @param string $labelsProperty
     * @param string $ownerProperty
     */
    public function __construct($labelsOwner, $labelsClass, $labelsProperty, $ownerProperty)
    {
        $this->labelsOwner    = $labelsOwner;
        $this->labelsClass    = $labelsClass;
        $this->labelsProperty = $labelsProperty;
        $this->ownerProperty  = $ownerProperty;
    }

    /**
     * {@inheritdoc}
     *
     * @param Label[] $value
     */
    public function transform($value)
    {
        if (!is_array($value) && !$value instanceof \Traversable) {
            return [];
        }

        $result = [];
        foreach ($value as $label) {
            $result[] = $label ? $label->getLabel() : '';
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

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        /** @var ArrayCollection $entities */
        $entities = clone $propertyAccessor->getValue($this->labelsOwner, $this->labelsProperty);
        $result   = [];

        foreach ($labels as $label) {
            $entity = $entities
                ->filter(function (Label $entity) use ($label) {
                    return $entity->getLabel() === $label;
                })
                ->first()
            ;

            if ($entity) {
                // remove used entity to proper handle duplicates
                $entities->removeElement($entity);
            } else {
                $entity = new $this->labelsClass();
                if (!$entity instanceof Label) {
                    throw new \InvalidArgumentException('Entity '.get_class($entity).' is not instance of '.Label::class);
                }

                $entity->setLabel($label);
                $propertyAccessor->setValue($entity, $this->ownerProperty, $this->labelsOwner);
            }

            $result[] = $entity;
        }

        return $result;
    }
}
