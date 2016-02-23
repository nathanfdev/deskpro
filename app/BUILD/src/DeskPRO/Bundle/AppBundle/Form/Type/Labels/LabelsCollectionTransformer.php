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

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

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
     * @param EntityManager $em
     * @param object        $labelsOwner
     * @param string        $labelsClass
     * @param string        $labelsProperty
     * @param string        $ownerProperty
     */
    public function __construct(EntityManager $em, $labelsOwner, $labelsClass, $labelsProperty, $ownerProperty)
    {
        $this->em             = $em;
        $this->labelsOwner    = $labelsOwner;
        $this->labelsClass    = $labelsClass;
        $this->labelsProperty = $labelsProperty;
        $this->ownerProperty  = $ownerProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($labels)
    {
        if (is_array($labels) || $labels instanceof \Traversable) {
            $result = [];
            foreach ($labels as $label) {
                if (is_string($label)) {
                    $result[] = $label;
                }
            }

            return $result;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($labels)
    {
        if (!$this->labelsOwner) {
            return;
        }

        $repository = $this->em->getRepository($this->labelsClass);

        $result = [];
        foreach ($labels as $label) {
            $entity = $repository->findOneBy([$this->ownerProperty => $this->labelsOwner, 'label' => $label]);
            if (!$entity) {
                $entity = $this->newLabel($this->labelsOwner, $label);
            }

            $result[] = $entity;
        }

        return $result;
    }

    /**
     * @param object $owner
     * @param string $label
     *
     * @return mixed
     */
    private function newLabel($owner, $label)
    {
        $new = new $this->labelsClass();

        $reflectionLabel = new \ReflectionProperty($this->labelsClass, 'label');
        $reflectionLabel->setAccessible(true);
        $reflectionLabel->setValue($new, $label);

        $reflectionOwner = new \ReflectionProperty($this->labelsClass, $this->ownerProperty);
        $reflectionOwner->setAccessible(true);
        $reflectionOwner->setValue($new, $owner);

        return $new;
    }
}
