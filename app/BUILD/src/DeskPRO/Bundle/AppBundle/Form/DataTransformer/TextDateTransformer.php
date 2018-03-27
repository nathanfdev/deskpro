<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class TextDateTransformer.
 */
class TextDateTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $format;

    /**
     * Constructor.
     *
     * @param string $format
     */
    public function __construct($format = \DateTime::ISO8601)
    {
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value instanceof \DateTime) {
            return;
        }

        return $value->format(\DateTime::ISO8601);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value === null || empty($value)) {
            return;
        } elseif ($value instanceof \DateTime) {
            return $value;
        }

        $date = \DateTime::createFromFormat(\DateTime::ISO8601, $value);

        if (!$date) {
            throw new TransformationFailedException('incorrect date format, use ISO 8601');
        }

        return $date;
    }
}
