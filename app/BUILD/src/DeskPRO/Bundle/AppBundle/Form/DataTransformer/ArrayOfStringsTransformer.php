<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class ArrayOfStringsTransformer.
 */
class ArrayOfStringsTransformer implements DataTransformerInterface
{
    /**
     * @var bool
     */
    private $unique_values;

    /**
     * Constructor.
     *
     * @param bool $unique_values
     */
    public function __construct($unique_values = false)
    {
        $this->unique_values = $unique_values;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            return [];
        }

        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new TransformationFailedException('Expected array');
        }

        $result = [];
        foreach ($value as $string) {
            if (!is_scalar($string)) {
                throw new TransformationFailedException('Expected scalar');
            }

            $string = (string) $string;
            $string = trim($string);

            $result[] = $string;
        }

        if ($this->unique_values) {
            $result = array_values(array_unique($result));
        }

        return $result;
    }
}
