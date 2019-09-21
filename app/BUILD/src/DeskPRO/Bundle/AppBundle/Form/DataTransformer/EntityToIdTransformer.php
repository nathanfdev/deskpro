<?php

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

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
     * @var bool
     */
    private $keepAsIds;

    /**
     * Constructor.
     *
     * @param EntityRepository $repo
     * @param bool $keepAsIds
     */
    public function __construct(EntityRepository $repo, $keepAsIds = false)
    {
        $this->repo = $repo;
        $this->keepAsIds = $keepAsIds;
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
        if ($this->keepAsIds) {
            if (empty($value)) {
                return [];
            }

            if (!(is_array($value) || $value instanceof \Traversable)) {
                throw new TransformationFailedException(
                    '$value was expected to be array or traversable during reverse transform'
                );
            }

            $ids = [];
            foreach ($value as $object) {
                if (is_object($object) && method_exists($object, 'getId')) {
                    $ids[] = $object->getId();
                } else {
                    $ids[] = $object;
                }
            }

            return $ids;
        }

        if (is_array($value) || $value instanceof \Traversable) {
            return $this->repo->findBy([
                'id' => $value,
            ]);
        }

        return $value ? $this->repo->find($value) : null;
    }
}
