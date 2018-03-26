<?php

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class EntityToIdTransformer.
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repo;

    /**
     * Constructor.
     *
     * @param EntityRepository $repo
     */
    public function __construct(EntityRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (is_array($value) || $value instanceof \Traversable) {
            $ids = is_array($value) ? [] : new ArrayCollection();
            foreach ($value as $item) {
                /* @var EntityInterface $item */
                $ids[] = is_object($item) ? $item->getId() : null;
            }

            return $ids;
        }

        return is_object($value) ? $value->getId() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (is_array($value) || $value instanceof \Traversable) {
            return $this->repo->findBy([
                'id' => $value,
            ]);
        }

        return $value ? $this->repo->find($value) : null;
    }
}
