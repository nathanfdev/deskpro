<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Application\DeskPRO\Entity\Blob;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class BlobAuthTransformer.
 */
class BlobAuthTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value instanceof Blob ? $value->getAuthId() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        /** @var \Application\DeskPRO\EntityRepository\Blob $repository */
        $repository = $this->em->getRepository(Blob::class);

        return $value ? $repository->getByAuthId($value) : null;
    }
}
