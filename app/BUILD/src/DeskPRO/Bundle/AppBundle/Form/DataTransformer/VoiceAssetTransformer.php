<?php

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class VoiceAssetTransformer.
 */
class VoiceAssetTransformer implements DataTransformerInterface
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
        return $value instanceof AbstractVoiceAsset ? $value->getAuth() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return;
        }

        return $this->em->getRepository(AbstractVoiceAsset::class)->findOneBy([
            'auth' => $value,
        ]);
    }
}
