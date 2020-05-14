<?php

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ChatRatingOverallTransformer.
 */
class ChatRatingOverallTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value === 10;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $value ? 10 : 1;
    }
}
